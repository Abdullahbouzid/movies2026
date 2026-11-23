<?php

namespace App\Support;

use App\Models\ClubBooking;
use App\Models\ClubPackage;
use App\Models\EventBooking;
use App\Models\EventPackage;

/**
 * Capacity accounting for club visits and event slots.
 *
 * Both sell a package that admits `people_count` guests during a fixed time
 * window. A package may declare a `capacity` (total guests per slot); zero
 * means unlimited so existing packages keep behaving as before.
 */
class SlotAvailability
{
    /**
     * Guests already booked into a club package slot on a date.
     */
    public static function clubBooked(ClubPackage $package, string $date): int
    {
        return (int) ClubBooking::query()
            ->where('club_id', $package->club_id)
            ->where('club_package_id', $package->id)
            ->whereDate('visit_date', $date)
            ->sum('people_count');
    }

    public static function clubHasRoom(ClubPackage $package, string $date): bool
    {
        $capacity = (int) $package->capacity;

        if ($capacity <= 0) {
            return true;
        }

        return self::clubBooked($package, $date) + max(1, (int) $package->people_count) <= $capacity;
    }

    /**
     * Guests already booked into an event slot (package + date + start time).
     */
    public static function eventBooked(EventPackage $package, string $date, string $startTime): int
    {
        return (int) EventBooking::query()
            ->where('event_package_id', $package->id)
            ->whereDate('event_date', $date)
            ->whereTime('start_time', substr($startTime, 0, 5) . ':00')
            ->sum('people_count');
    }

    public static function eventHasRoom(EventPackage $package, string $date, string $startTime): bool
    {
        $capacity = (int) $package->capacity;

        if ($capacity <= 0) {
            return true;
        }

        return self::eventBooked($package, $date, $startTime) + max(1, (int) $package->people_count) <= $capacity;
    }
}
