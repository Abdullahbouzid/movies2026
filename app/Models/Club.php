<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    protected $fillable = [
        'name', 'location', 'description', 'image',
        'color_from', 'color_to', 'badge', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(ClubPackage::class)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function allPackages(): HasMany
    {
        return $this->hasMany(ClubPackage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ClubBooking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function minPrice(): ?float
    {
        $price = $this->packages->min('price');

        return $price === null ? null : (float) $price;
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
     * @return array{0:string, 1:string}
     */
    private function fallbackGradient(): array
    {
        $palette = [
            ['#0f766e', '#22d3ee'],
            ['#4c1d95', '#a855f7'],
            ['#92400e', '#f59e0b'],
            ['#9f1239', '#fb7185'],
            ['#1e3a8a', '#3b82f6'],
        ];

        return $palette[($this->id ?? 0) % count($palette)];
    }
}
