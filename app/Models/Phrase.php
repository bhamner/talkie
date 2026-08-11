<?php

namespace App\Models;

use Database\Factories\PhraseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phrase extends Model
{
    /** @use HasFactory<PhraseFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_id',
        'text',
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
