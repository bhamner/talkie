<?php

namespace App\Models;

use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
        'slug',
        'icon',
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

    public static function uniqueSlugFor(?int $userId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'folder';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->when(
                $userId === null,
                fn (Builder $query) => $query->whereNull('user_id'),
                fn (Builder $query) => $query->where('user_id', $userId),
            )
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected static function booted(): void
    {
        static::creating(function (Menu $menu): void {
            if (blank($menu->slug)) {
                $menu->slug = static::uniqueSlugFor($menu->user_id, $menu->name);
            }
        });

        static::updating(function (Menu $menu): void {
            if ($menu->isDirty('name')) {
                $menu->slug = static::uniqueSlugFor($menu->user_id, $menu->name, $menu->id);
            }
        });
    }
}
