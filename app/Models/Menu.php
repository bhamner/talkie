<?php

namespace App\Models;

use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function words(): HasMany
    {
        return $this->hasMany(Word::class)->orderBy('sort_order');
    }

    public function phrases(): HasMany
    {
        return $this->hasMany(Phrase::class)->orderBy('sort_order');
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
