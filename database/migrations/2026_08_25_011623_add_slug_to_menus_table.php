<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('slug')->default('')->after('name');
            $table->index(['user_id', 'slug']);
        });

        $used = [];

        DB::table('menus')->orderBy('id')->get(['id', 'user_id', 'name'])->each(function (object $menu) use (&$used): void {
            $ownerKey = $menu->user_id === null ? 'template' : (string) $menu->user_id;
            $used[$ownerKey] ??= [];

            $base = Str::slug((string) $menu->name) ?: 'folder';
            $slug = $base;
            $suffix = 2;

            while (in_array($slug, $used[$ownerKey], true)) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }

            $used[$ownerKey][] = $slug;

            DB::table('menus')->where('id', $menu->id)->update(['slug' => $slug]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'slug']);
            $table->dropColumn('slug');
        });
    }
};
