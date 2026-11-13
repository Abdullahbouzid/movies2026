<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_COMING_SOON = 'coming_soon';

    protected $fillable = [
        'name', 'image', 'poster_from', 'poster_to', 'accent_icon', 'genre_id', 'description',
        'duration', 'rating', 'ratings_count', 'age_rating', 'status', 'badge', 'release_date',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'release_date' => 'date',
    ];

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'movie_package')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeComingSoon($query)
    {
        return $query->where('status', self::STATUS_COMING_SOON);
    }

    public function isComingSoon(): bool
    {
        return $this->status === self::STATUS_COMING_SOON;
    }

    public function priceForPackage(int $packageId): ?float
    {
        $package = $this->packages->firstWhere('id', $packageId);

        return $package?->pivot?->price !== null ? (float) $package->pivot->price : null;
    }

    public function minPrice(): ?float
    {
        $prices = $this->packages->pluck('pivot.price')->filter(fn ($p) => $p !== null);

        return $prices->isEmpty() ? null : (float) $prices->min();
    }

    public function posterFrom(): string
    {
        return $this->poster_from ?: $this->fallbackGradient()[0];
    }

    public function posterTo(): string
    {
        return $this->poster_to ?: $this->fallbackGradient()[1];
    }

    /**
     * Deterministic red-family gradient so every movie gets a distinct poster
     * even when no explicit colors are stored.
     *
     * @return array{0:string, 1:string}
     */
    private function fallbackGradient(): array
    {
        $palette = [
            ['#eb1e23', '#7a0d10'],
            ['#c4171c', '#eb1e23'],
            ['#8b1115', '#e71d24'],
            ['#e71d24', '#a81216'],
            ['#5c0a0d', '#e71d24'],
            ['#a81216', '#e71d24'],
            ['#7a0d10', '#c4171c'],
        ];

        return $palette[$this->id % count($palette)];
    }

    public function stars(): string
    {
        $rating = (float) $this->rating;
        $full = (int) floor($rating / 2);
        $half = ($rating / 2 - $full) >= 0.5 ? 1 : 0;

        return str_repeat('★', $full) . ($half ? '½' : '') . str_repeat('☆', 5 - $full - $half);
    }

    public function priceFor(string $packageName): ?float
    {
        $package = $this->packages->firstWhere('name', $packageName);

        return $package?->pivot?->price !== null ? (float) $package->pivot->price : null;
    }
}
