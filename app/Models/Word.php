<?php

namespace App\Models;

use Database\Factories\WordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Word extends Model
{
    /** @use HasFactory<WordFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_id',
        'label',
        'icon',
        'speak_text',
        'sort_order',
        'is_builtin',
        'is_hidden',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_builtin' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function textToSpeak(): string
    {
        return $this->speak_text ?: $this->label;
    }

    public function scopeTemplate($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }
}
