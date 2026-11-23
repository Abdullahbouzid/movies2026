<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Movie;
use App\Models\Showtime;

/**
 * Seat accounting for cinema showings.
 *
 * Every showtime has `seats_capacity` seats per date. A booking consumes as
 * many seats as its package provides (`Package::seats_count`), snapshotted onto
 * `bookings.seats` so later package edits do not rewrite history.
 */
class SeatAvailability
{
    /**
     * Seats already sold for a showtime on a date.
     */
    public static function sold(Showtime $showtime, string $date): int
    {
        return (int) Booking::query()
            ->where('showtime_id', $showtime->id)
            ->whereDate('show_date', $date)
            ->sum('seats');
    }

    public static function remaining(Showtime $showtime, string $date): int
    {
        $capacity = max(0, (int) $showtime->seats_capacity);

        return max(0, $capacity - self::sold($showtime, $date));
    }

    public static function hasRoomFor(Showtime $showtime, string $date, int $seats): bool
    {
        return self::remaining($showtime, $date) >= max(1, $seats);
    }

    /**
     * Remaining seats per showtime id, for a set of dates, in one query.
     *
     * @param  array<int, int>  $showtimeIds
     * @param  array<int, string>  $dates
     * @return array<string, array<int, int>> keyed by date, then showtime id
     */
    public static function remainingForShowtimes(array $showtimeIds, array $dates): array
    {
        if ($showtimeIds === [] || $dates === []) {
            return [];
        }

        $sold = Booking::query()
            ->whereIn('showtime_id', $showtimeIds)
            ->whereDate('show_date', '>=', min($dates))
            ->whereDate('show_date', '<=', max($dates))
            ->get(['show_date', 'showtime_id', 'seats'])
            ->groupBy(fn ($row) => $row->show_date->toDateString())
            ->map(fn ($perDate) => $perDate
                ->groupBy('showtime_id')
                ->map(fn ($perSlot) => (int) $perSlot->sum('seats'))
                ->all())
            ->all();

        return array_intersect_key($sold, array_flip($dates));
    }

    /**
     * Sold seats grouped per movie across the next seven days.
     *
     * @param  \Illuminate\Support\Collection<int, Movie>  $movies
     * @param  array<int, array{iso:string}>  $weekDays
     * @return array<int, array<string, array<int, int>>>
     */
    public static function weekGrid($movies, array $weekDays): array
    {
        $dates = array_column($weekDays, 'iso');
        $ids = $movies->flatMap(fn (Movie $m) => $m->showtimes->pluck('id'))->unique()->values()->all();
        $sold = self::remainingForShowtimes($ids, $dates);

        $grid = [];

        foreach ($movies as $movie) {
            foreach ($weekDays as $day) {
                foreach ($movie->showtimes as $showtime) {
                    if ($showtime->show_date?->toDateString() !== $day['iso']) {
                        continue;
                    }

                    $used = $sold[$day['iso']][$showtime->id] ?? 0;
                    $grid[$movie->id][$day['iso']][$showtime->id] = max(0, (int) $showtime->seats_capacity - $used);
                }
            }
        }

        return $grid;
    }
}
