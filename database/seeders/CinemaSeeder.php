<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Package;
use App\Models\Showtime;
use App\Models\User;
use App\Support\PosterFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CinemaSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@cinema.test'],
            ['name' => 'مدير النظام', 'password' => Hash::make('password')],
        );

        $packages = collect([
            ['name' => 'الباقة العادية', 'seats_count' => 1, 'includes' => 'مقعد عادي + تذكرة دخول واحدة'],
            ['name' => 'الباقة الذهبية', 'seats_count' => 2, 'includes' => 'مقعدان مريحان + فشار ومشروب لكل فرد'],
            ['name' => 'باقة VIP', 'seats_count' => 2, 'includes' => 'مقاعد VIP مريحة + مشروب وفشار + خدمة مميزة'],
        ])->map(fn ($p) => Package::create($p));

        $genreNames = ['أكشن', 'إثارة', 'خيال علمي', 'رومانسي', 'كوميدي', 'غموض', 'دراما', 'مغامرة', 'رسوم متحركة'];
        $genres = collect($genreNames)->mapWithKeys(
            fn ($name) => [$name => Genre::create(['name' => $name])]
        );

        $movies = [
            ['name' => 'ظل المدينة', 'genre' => 'إثارة', 'rating' => 8.6, 'votes' => 1240, 'duration' => 128, 'age' => '+16', 'status' => 'available', 'base' => 35, 'g1' => '#eb1e23', 'g2' => '#7a0d10'],
            ['name' => 'رحلة الصحراء', 'genre' => 'مغامرة', 'rating' => 8.1, 'votes' => 890, 'duration' => 142, 'age' => '+12', 'status' => 'available', 'base' => 30, 'g1' => '#c4171c', 'g2' => '#eb1e23', 'badge' => 'new'],
            ['name' => 'النجم الأخير', 'genre' => 'خيال علمي', 'rating' => 9.0, 'votes' => 2100, 'duration' => 155, 'age' => '+12', 'status' => 'available', 'base' => 45, 'g1' => '#8b1115', 'g2' => '#e71d24', 'badge' => 'trending'],
            ['name' => 'قلوب حائرة', 'genre' => 'رومانسي', 'rating' => 7.4, 'votes' => 620, 'duration' => 112, 'age' => '+12', 'status' => 'available', 'base' => 30, 'g1' => '#e71d24', 'g2' => '#a81216'],
            ['name' => 'المهمة: صفر', 'genre' => 'أكشن', 'rating' => 8.8, 'votes' => 1850, 'duration' => 136, 'age' => '+16', 'status' => 'available', 'base' => 40, 'g1' => '#5c0a0d', 'g2' => '#e71d24', 'badge' => 'trending'],
            ['name' => 'ضحكة الليل', 'genre' => 'كوميدي', 'rating' => 7.9, 'votes' => 540, 'duration' => 105, 'age' => '+12', 'status' => 'available', 'base' => 25, 'g1' => '#e71d24', 'g2' => '#c4171c'],
            ['name' => 'بحر الأسرار', 'genre' => 'غموض', 'rating' => 8.3, 'votes' => 980, 'duration' => 121, 'age' => '+16', 'status' => 'available', 'base' => 35, 'g1' => '#a81216', 'g2' => '#e71d24', 'badge' => 'popular'],
            ['name' => 'عودة البطل', 'genre' => 'دراما', 'rating' => 8.5, 'votes' => 1120, 'duration' => 133, 'age' => '+12', 'status' => 'available', 'base' => 35, 'g1' => '#7a0d10', 'g2' => '#c4171c'],
            ['name' => 'المدينة الحالمة', 'genre' => 'رسوم متحركة', 'rating' => 8.2, 'votes' => 780, 'duration' => 98, 'age' => 'الجميع', 'status' => 'available', 'base' => 20, 'g1' => '#e71d24', 'g2' => '#7a0d10', 'badge' => 'new'],
            ['name' => 'الشعلة الأخيرة', 'genre' => 'أكشن', 'rating' => 0, 'votes' => 0, 'duration' => 138, 'age' => '+16', 'status' => 'coming_soon', 'base' => 40, 'g1' => '#5c0a0d', 'g2' => '#eb1e23', 'icon' => '🔥', 'release' => '2026-11-15'],
            ['name' => 'أعماق المحيط', 'genre' => 'مغامرة', 'rating' => 0, 'votes' => 0, 'duration' => 124, 'age' => '+12', 'status' => 'coming_soon', 'base' => 35, 'g1' => '#1e3a8a', 'g2' => '#0ea5e9', 'icon' => '🌊', 'release' => '2026-11-22'],
            ['name' => 'أحلام الغد', 'genre' => 'خيال علمي', 'rating' => 0, 'votes' => 0, 'duration' => 145, 'age' => '+12', 'status' => 'coming_soon', 'base' => 35, 'g1' => '#4c1d95', 'g2' => '#a855f7', 'icon' => '✨', 'release' => '2026-12-05'],
        ];

        $timePool = [
            ['10:00', '12:20'],
            ['11:30', '13:50'],
            ['14:00', '16:20'],
            ['16:30', '18:50'],
            ['19:00', '21:20'],
            ['20:30', '22:50'],
            ['21:30', '23:50'],
            ['23:00', '01:20'],
        ];

        foreach ($movies as $i => $data) {
            $movie = Movie::create([
                'name' => $data['name'],
                'genre_id' => $genres[$data['genre']]->id,
                'description' => 'وصف تجريبي لفيلم «' . $data['name'] . '» — تجربة سينمائية مميزة على شاشات سينما بلاس.',
                'duration' => $data['duration'],
                'rating' => $data['rating'],
                'ratings_count' => $data['votes'],
                'age_rating' => $data['age'],
                'status' => $data['status'],
                'badge' => $data['badge'] ?? null,
                'poster_from' => $data['g1'],
                'poster_to' => $data['g2'],
                'accent_icon' => $data['icon'] ?? null,
                'release_date' => isset($data['release']) ? \Illuminate\Support\Carbon::parse($data['release']) : now()->subDays(10),
                'image' => 'movies/movie-' . ($i + 1) . '.jpg',
            ]);

            PosterFactory::make($movie->image, $data['g1'], $data['g2']);

            foreach ($packages as $pi => $package) {
                $movie->packages()->attach($package->id, [
                    'price' => $data['base'] + ($pi * 25),
                ]);
            }

            if ($data['status'] === 'available') {
                // Dated showtimes covering the configured rolling horizon so the
                // public week grid is never empty; `showtimes:generate` keeps it topped up.
                $horizon = max(6, (int) config('cinema.schedule_horizon_days', 14));
                foreach (range(0, $horizon - 1) as $offset) {
                    $date = now()->addDays($offset);

                    foreach ($timePool as $ti => $t) {
                        Showtime::create([
                            'movie_id' => $movie->id,
                            'show_date' => $date->toDateString(),
                            'day_of_week' => $date->dayOfWeek,
                            'start_time' => $t[0],
                            'end_time' => $t[1],
                            'seats_capacity' => 40 + (($i + $ti) % 4) * 20,
                        ]);
                    }
                }
            }
        }
    }
}
