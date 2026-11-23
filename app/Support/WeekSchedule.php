<?php

namespace App\Support;

use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Support\Carbon;

/**
 * Builds a dated weekly cinema schedule.
 *
 * Showtimes are stored per concrete date (not per weekday), so the public grid
 * always knows which calendar day a slot belongs to. This class fills a range
 * of days from a template of start times.
 */
class WeekSchedule
{
    /**
     * Generate showtimes for every available movie across a date range.
     *
     * Each movie gets one slot per start time on each day. Existing slots for
     * the same movie/date/time are skipped, so re-running is safe.
     *
     * @param  array<int, string>  $startTimes  e.g. ['11:30', '14:00']
     * @return int number of showtimes created
     */
    public static function generate(array $startTimes, ?Carbon $from = null, int $days = 7, ?int $seatsCapacity = null): int
    {
        $from ??= today();
        $capacity = $seatsCapacity ?? (int) config('cinema.seats_capacity', 60);
        $created = 0;

        $movies = Movie::query()
            ->available()
            ->get(['id', 'duration']);

        foreach ($movies as $movie) {
            $duration = max(1, (int) ($movie->duration ?: 120));

            for ($offset = 0; $offset < $days; $offset++) {
                $date = $from->copy()->addDays($offset)->startOfDay();

                foreach ($startTimes as $start) {
                    $start = substr(trim($start), 0, 5);

                    if (! preg_match('/^\d{2}:\d{2}$/', $start)) {
                        continue;
                    }

                    $exists = Showtime::query()
                        ->where('movie_id', $movie->id)
                        ->whereDate('show_date', $date->toDateString())
                        ->whereTime('start_time', $start . ':00')
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    Showtime::create([
                        'movie_id' => $movie->id,
                        'show_date' => $date->toDateString(),
                        'day_of_week' => $date->dayOfWeek,
                        'start_time' => $start,
                        'end_time' => Carbon::parse($start)->addMinutes($duration)->format('H:i'),
                        'seats_capacity' => $capacity,
                    ]);

                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * Showtime counts per calendar date, used by the admin summary.
     *
     * @return array<string, int>
     */
    public static function countsByDate(int $days = 7): array
    {
        $counts = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $date = today()->addDays($offset);
            $counts[$date->format('d/m/Y')] = Showtime::query()
                ->whereDate('show_date', $date->toDateString())
                ->count();
        }

        return $counts;
    }
}
