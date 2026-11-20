<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Game extends Model
{
    public const BADGES = [
        '' => 'بدون',
        'trending' => 'رائج 🔥',
        'new' => 'جديد ✨',
        'popular' => 'مميز ⭐',
    ];

    protected $fillable = [
        'name', 'slug', 'icon', 'image', 'description', 'meta',
        'color_from', 'color_to', 'badge', 'rating', 'ratings_count',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $game) {
            if (blank($game->slug)) {
                $game->slug = static::uniqueSlug($game->name, $game->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'game';
        $slug = $base;
        $i = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function prices(): HasMany
    {
        return $this->hasMany(GamePrice::class)->orderBy('sort_order')->orderBy('minutes');
    }

    public function playTimes(): HasMany
    {
        return $this->hasMany(PlayTime::class)->orderBy('day_of_week')->orderBy('start_time');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(GameBooking::class);
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function minPrice(): ?float
    {
        $price = $this->prices->min('price');

        return $price === null ? null : (float) $price;
    }

    public function defaultPrice(): ?GamePrice
    {
        return $this->prices->firstWhere('is_default', true) ?? $this->prices->first();
    }

    public function colorFrom(): string
    {
        return $this->color_from ?: $this->fallbackGradient()[0];
    }

    public function colorTo(): string
    {
        return $this->color_to ?: $this->fallbackGradient()[1];
    }

    /**
     * Deterministic palette so every game gets a distinct card even when no
     * explicit colors are stored.
     *
     * @return array{0:string, 1:string}
     */
    private function fallbackGradient(): array
    {
        $palette = [
            ['#eb1e23', '#8b1115'],
            ['#0f766e', '#22d3ee'],
            ['#92400e', '#f59e0b'],
            ['#1e3a8a', '#3b82f6'],
            ['#4c1d95', '#a855f7'],
            ['#9f1239', '#fb7185'],
        ];

        return $palette[($this->id ?? 0) % count($palette)];
    }
}
