<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'preferred_name',
        'email',
        'password',
        'provider',
        'provider_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function words(): HasMany
    {
        return $this->hasMany(Word::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function hasCompletedOnboarding(): bool
    {
        return filled($this->preferred_name)
            && $this->settings?->onboarding_completed_at !== null;
    }

    public function onboardingStep(): string
    {
        if (! filled($this->preferred_name)) {
            return 'name';
        }

        if ($this->settings?->onboarding_completed_at === null) {
            return 'voice';
        }

        return 'complete';
    }
}
