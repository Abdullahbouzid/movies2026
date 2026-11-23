<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventPackage extends Model
{
    protected $fillable = [
        'name', 'includes', 'people_count', 'price', 'capacity', 'image',
        'is_default', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'people_count' => 'integer',
        'capacity' => 'integer',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(EventBooking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
