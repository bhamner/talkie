<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'voice_id',
        'voice_uri',
        'voice_name',
        'extras',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'extras' => 'array',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
