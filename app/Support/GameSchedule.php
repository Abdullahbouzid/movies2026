<?php

namespace App\Support;

use App\Models\Game;
use App\Models\GameBooking;
use App\Models\PlayTime;
use Illuminate\Support\Carbon;

/**
 * Slot availability for gaming stations.
 *
 * A game owns `devices_count` stations. Each (game, date, play time) slot has
 * one seat per station, so a slot is bookable until every station is taken.
 */
class GameSchedule
{
    /**
     * Booked device numbers per play time id for a game on a date.
     *
     * @return array<int, array<int, int>>
     */
    public static function takenDevices(Game $game, Carbon|string $date): array
    {
        $date = $date instanceof Carbon ? $date->toDateString() : $date;

        return GameBooking::query()
            ->where('game_id', $game->id)
            ->whereDate('play_date', $date)
            ->whereNotNull('play_time_id')
            ->whereNotNull('device_number')
            ->get(['play_time_id', 'device_number'])
            ->groupBy('play_time_id')
            ->map(fn ($rows) => $rows->pluck('device_number')->map(fn ($n) => (int) $n)->sort()->values()->all())
            ->all();
    }

    /**
     * Remaining device numbers for a slot, in ascending order.
     *
     * @param  array<int, int>  $taken
     * @return array<int, int>
     */
    public static function freeDevices(Game $game, array $taken): array
    {
        $total = max(1, (int) $game->devices_count);
        $free = [];

        for ($device = 1; $device <= $total; $device++) {
            if (! in_array($device, $taken, true)) {
                $free[] = $device;
            }
        }

        return $free;
    }

    /**
     * Week grid used by the public page: for each of the next seven days,
     * every active play time with its free device count.
     *
     * @return array<int, array<int, array{id:int, time:string, end:string, free:int, total:int}>>
     */
    public static function weekAvailability(Game $game, array $weekDays): array
    {
        $byDay = [];
        $takenByDate = self::takenDevicesForRange(
            $game,
            collect($weekDays)->pluck('iso')->all(),
        );

        foreach ($weekDays as $index => $day) {
            $taken = $takenByDate[$day['iso']] ?? [];

            $slots = $game->playTimes
                ->where('day_of_week', $day['dow'])
                ->sortBy('start_time')
                ->map(fn (PlayTime $slot) => [
                    'id' => $slot->id,
                    'time' => $slot->start_time_short,
                    'end' => $slot->end_time_short,
                    'free' => count(self::freeDevices($game, $taken[$slot->id] ?? [])),
                    'total' => max(1, (int) $game->devices_count),
                ])
                ->values()
                ->all();

            $byDay[$index] = $slots;
        }

        return $byDay;
    }

    /**
     * Booked device numbers for every date in the range, in one query.
     *
     * @param  array<int, string>  $dates
     * @return array<string, array<int, array<int, int>>> keyed by date, then play time id
     */
    public static function takenDevicesForRange(Game $game, array $dates): array
    {
        if ($dates === []) {
            return [];
        }

        $rows = GameBooking::query()
            ->where('game_id', $game->id)
            ->whereIn('play_date', $dates)
            ->whereNotNull('play_time_id')
            ->whereNotNull('device_number')
            ->get(['play_date', 'play_time_id', 'device_number']);

        return $rows
            ->groupBy(fn ($row) => $row->play_date->toDateString())
            ->map(fn ($perDate) => $perDate
                ->groupBy('play_time_id')
                ->map(fn ($perSlot) => $perSlot->pluck('device_number')->map(fn ($n) => (int) $n)->sort()->values()->all())
                ->all())
            ->all();
    }

    public static function isSlotBookable(Game $game, PlayTime $slot, Carbon|string $date): bool
    {
        return self::freeDevices($game, self::takenDevices($game, $date)[$slot->id] ?? []) !== [];
    }
}
