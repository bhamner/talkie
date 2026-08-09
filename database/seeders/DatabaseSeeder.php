<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\BoardTemplateService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(BoardTemplateSeeder::class);

        $user = User::factory()->create([
            'name' => 'Test User',
            'preferred_name' => 'Test',
            'email' => 'test@example.com',
            'provider' => 'google',
            'provider_id' => 'seed-test-user',
        ]);

        app(BoardTemplateService::class)->copyToUser($user);

        $user->settings()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'voice_id' => 'device-default',
                'voice_name' => 'Friendly',
                'onboarding_completed_at' => now(),
            ]
        );
    }
}
