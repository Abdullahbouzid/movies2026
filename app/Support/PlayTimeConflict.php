<?php

namespace App\Support;

use App\Models\PlayTime;

/**
 * Detects overlapping play times for the same game on the same weekday.
 *
 * Two slots of one game must never overlap, otherwise the public page would
 * advertise the same hour twice and the per-device counters would contradict
 * each other.
 */
class PlayTimeConflict
{
    /** The first slot that overlaps the given one, if any. */
    public static function clash(
        int $gameId,
        int|string $dayOfWeek,
        mixed $startTime,
        mixed $endTime,
        ?int $ignoreId = null,
    ): ?PlayTime {
        $start = self::normalize($startTime);
        $end = self::normalize($endTime);

        if (! $start || ! $end || $start >= $end) {
            return null;
        }

        return PlayTime::query()
            ->where('game_id', $gameId)
            ->where('day_of_week', $dayOfWeek)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->orderBy('start_time')
            ->first();
    }

    /** Normalises "9:00", "09:00" and "09:00:00" to a comparable "H:i:s". */
    private static function normalize(mixed $time): ?string
    {
        if (! is_string($time) || trim($time) === '') {
            return null;
        }

        return strlen($time) === 5 ? $time.':00' : substr($time, 0, 8);
    }
}
