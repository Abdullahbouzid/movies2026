<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    /** Services a branch can offer; also the stored values in `services`. */
    public const SERVICES = [
        'cinema' => 'السينما',
        'games' => 'الألعاب',
        'club' => 'النادي الاجتماعي',
        'events' => 'المناسبات',
    ];

    protected $fillable = [
        'name', 'location', 'phone', 'image', 'services',
        'is_main', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'services' => 'array',
        'is_main' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $branch): void {
            $branch->services = array_values(array_intersect(
                array_keys(self::SERVICES),
                (array) $branch->services,
            ));

            if ($branch->is_main) {
                static::query()
                    ->when($branch->exists, fn ($q) => $q->whereKeyNot($branch->getKey()))
                    ->update(['is_main' => false]);
            }
        });
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function gameBookings(): HasMany
    {
        return $this->hasMany(GameBooking::class);
    }

    public function clubBookings(): HasMany
    {
        return $this->hasMany(ClubBooking::class);
    }

    public function eventBookings(): HasMany
    {
        return $this->hasMany(EventBooking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function offers(string $service): bool
    {
        return in_array($service, (array) $this->services, true);
    }

    /** @return array<int, string> */
    public function serviceLabels(): array
    {
        return array_values(array_map(
            fn ($service) => self::SERVICES[$service],
            array_intersect(array_keys(self::SERVICES), (array) $this->services),
        ));
    }

    public static function main(): ?self
    {
        return static::query()->where('is_main', true)->first();
    }
}
