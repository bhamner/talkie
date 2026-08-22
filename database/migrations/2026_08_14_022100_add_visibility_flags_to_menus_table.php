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
        Schema::table('menus', function (Blueprint $table) {
            $table->boolean('is_builtin')->default(false)->after('sort_order');
            $table->boolean('is_hidden')->default(false)->after('is_builtin');
            $table->index(['user_id', 'parent_id', 'is_hidden']);
        });

        DB::table('menus')->whereNull('user_id')->update(['is_builtin' => true]);

        $templateNames = DB::table('menus')
            ->whereNull('user_id')
            ->pluck('name')
            ->unique()
            ->all();

        if ($templateNames !== []) {
            DB::table('menus')
                ->whereNotNull('user_id')
                ->whereIn('name', $templateNames)
                ->update(['is_builtin' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'parent_id', 'is_hidden']);
            $table->dropColumn(['is_builtin', 'is_hidden']);
        });
    }
};
