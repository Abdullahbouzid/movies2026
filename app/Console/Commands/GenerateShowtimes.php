<?php

namespace App\Console\Commands;

use App\Models\Showtime;
use App\Support\WeekSchedule;
use Illuminate\Console\Command;

class GenerateShowtimes extends Command
{
    protected $signature = 'showtimes:generate
                            {--days= : Days ahead to populate (defaults to config)}
                            {--prune : Delete showtimes older than today}';

    protected $description = 'Top up the rolling cinema schedule so the public booking grid never runs dry';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('cinema.schedule_horizon_days', 14));
        $slots = (array) config('cinema.showtime_slots', []);

        $created = WeekSchedule::generate($slots, today(), $days);
        $this->info('أنشأ ' . $created . ' عرضاً خلال ' . $days . ' يوماً القادمة.');

        if ($this->option('prune')) {
            $pruned = Showtime::query()
                ->whereDate('show_date', '<', today()->toDateString())
                ->delete();

            $this->info('حُذف ' . $pruned . ' عرضاً منتهي الصلاحية.');
        }

        return self::SUCCESS;
    }
}
