<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Columns are also defined on the base user_settings migration for fresh installs.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('user_settings', 'voice_id')) {
            Schema::table('user_settings', function (Blueprint $table) {
                $table->string('voice_id')->nullable()->after('user_id');
            });
        }

        if (! Schema::hasColumn('user_settings', 'onboarding_completed_at')) {
            Schema::table('user_settings', function (Blueprint $table) {
                $table->timestamp('onboarding_completed_at')->nullable()->after('extras');
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
