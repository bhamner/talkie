<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('words', function (Blueprint $table) {
            $table->boolean('is_builtin')->default(false)->after('sort_order');
            $table->boolean('is_hidden')->default(false)->after('is_builtin');
            $table->index(['user_id', 'menu_id', 'is_hidden']);
        });

        Schema::table('phrases', function (Blueprint $table) {
            $table->boolean('is_builtin')->default(false)->after('sort_order');
            $table->boolean('is_hidden')->default(false)->after('is_builtin');
            $table->index(['user_id', 'menu_id', 'is_hidden']);
        });

        DB::table('words')->whereNull('user_id')->update(['is_builtin' => true]);
        DB::table('phrases')->whereNull('user_id')->update(['is_builtin' => true]);

        $templateLabels = DB::table('words')
            ->whereNull('user_id')
            ->pluck('label')
            ->map(fn (string $label) => strtolower($label))
            ->unique()
            ->all();

        if ($templateLabels !== []) {
            DB::table('words')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->chunkById(200, function ($words) use ($templateLabels): void {
                    foreach ($words as $word) {
                        if (in_array(strtolower($word->label), $templateLabels, true)) {
                            DB::table('words')->where('id', $word->id)->update(['is_builtin' => true]);
                        }
                    }
                });
        }

        $templateTexts = DB::table('phrases')
            ->whereNull('user_id')
            ->pluck('text')
            ->unique()
            ->all();

        if ($templateTexts !== []) {
            DB::table('phrases')
                ->whereNotNull('user_id')
                ->whereIn('text', $templateTexts)
                ->update(['is_builtin' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('words', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'menu_id', 'is_hidden']);
            $table->dropColumn(['is_builtin', 'is_hidden']);
        });

        Schema::table('phrases', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'menu_id', 'is_hidden']);
            $table->dropColumn(['is_builtin', 'is_hidden']);
        });
    }
};
