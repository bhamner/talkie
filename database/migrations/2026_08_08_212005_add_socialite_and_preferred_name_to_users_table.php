<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Columns are also defined on the base users migration for fresh installs.
     * This migration is a no-op when those columns already exist.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'preferred_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('preferred_name')->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('users', 'provider')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('provider')->nullable()->after('password');
                $table->string('provider_id')->nullable()->after('provider');
                $table->unique(['provider', 'provider_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty — base migration owns the canonical schema.
    }
};
