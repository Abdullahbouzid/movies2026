<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\PlayTime;
use App\Models\Tournament;
use App\Models\TournamentAnnouncement;
use App\Models\TournamentParticipant;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            ['name' => 'PlayStation 5', 'icon' => '🎮', 'meta' => 'أحدث جهاز ألعاب • شاشة 4K', 'c1' => '#eb1e23', 'c2' => '#8b1115', 'badge' => 'trending', 'rating' => 4.9, 'votes' => 120, 'prices' => [[30, 15], [60, 25], [120, 45]]],
            ['name' => 'تجربة الواقع الافتراضي', 'icon' => '🥽', 'meta' => 'انغماس كامل • سماعات 3D', 'c1' => '#0f766e', 'c2' => '#22d3ee', 'badge' => 'new', 'rating' => 4.8, 'votes' => 85, 'prices' => [[30, 40], [60, 70]]],
            ['name' => 'بلياردو', 'icon' => '🎱', 'meta' => 'طاولات احترافية • مضارب عالية الجودة', 'c1' => '#92400e', 'c2' => '#f59e0b', 'badge' => 'popular', 'rating' => 4.6, 'votes' => 95, 'prices' => [[30, 12], [60, 20], [120, 35]]],
            ['name' => 'Foosball', 'icon' => '⚽', 'meta' => 'تحدي أصدقاء • كرة طاولة', 'c1' => '#1e3a8a', 'c2' => '#3b82f6', 'badge' => null, 'rating' => 4.5, 'votes' => 70, 'prices' => [[30, 10], [60, 15]]],
            ['name' => 'محاكي سباق', 'icon' => '🏎️', 'meta' => 'عجلة قيادة • شاشة منحنية', 'c1' => '#4c1d95', 'c2' => '#a855f7', 'badge' => 'trending', 'rating' => 4.9, 'votes' => 110, 'prices' => [[30, 35], [60, 60]]],
            ['name' => 'سهام إلكترونية', 'icon' => '🎯', 'meta' => 'حساب نقاط تلقائي • لوحات Dart', 'c1' => '#9f1239', 'c2' => '#fb7185', 'badge' => null, 'rating' => 4.4, 'votes' => 60, 'prices' => [[30, 11], [60, 18]]],
        ];

        $playSlots = [
            ['12:00', '14:00'],
            ['14:00', '16:00'],
            ['16:00', '18:00'],
            ['18:00', '20:00'],
            ['20:00', '22:00'],
            ['22:00', '00:00'],
        ];

        $created = [];

        foreach ($games as $i => $data) {
            $game = Game::create([
                'name' => $data['name'],
                'icon' => $data['icon'],
                'meta' => $data['meta'],
                'description' => $data['meta'] . ' — متعة وتحدٍّ في صالة ألعاب سينما بلاس.',
                'color_from' => $data['c1'],
                'color_to' => $data['c2'],
                'badge' => $data['badge'],
                'rating' => $data['rating'],
                'ratings_count' => $data['votes'],
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);

            foreach ($data['prices'] as $index => [$minutes, $price]) {
                $game->prices()->create([
                    'label' => $minutes === 30 ? '30 دقيقة' : ($minutes === 60 ? 'ساعة' : 'ساعتان'),
                    'minutes' => $minutes,
                    'price' => $price,
                    'is_default' => $index === 0,
                    'sort_order' => $index + 1,
                ]);
            }

            foreach (range(0, 6) as $day) {
                foreach ($playSlots as $slot) {
                    $game->playTimes()->create([
                        'day_of_week' => $day,
                        'start_time' => $slot[0],
                        'end_time' => $slot[1],
                        'is_active' => true,
                    ]);
                }
            }

            $created[$game->name] = $game;
        }

        $fifa = Tournament::create([
            'name' => 'بطولة FIFA الأسبوعية',
            'game_id' => $created['PlayStation 5']->id,
            'description' => 'كل خميس مساءً — جوائز قيّمة للفائزين، تسجيل مفتوح لجميع اللاعبين.',
            'participants_count' => 8,
            'format' => 'knockout',
            'status' => 'open',
            'entry_fee' => 10,
            'prize' => 500,
            'starts_on' => now()->addWeek()->next('Thursday')->toDateString(),
            'starts_at' => '20:00',
        ]);

        foreach (['أحمد محمد علي', 'يوسف الصديق', 'عمر الفيتوري', 'سالم القذافي', 'زيد الهادي', 'خالد المنصوري', 'نبيل الشريف', 'طارق الزوي'] as $name) {
            TournamentParticipant::create(['tournament_id' => $fifa->id, 'name' => $name]);
        }

        TournamentAnnouncement::create([
            'tournament_id' => $fifa->id,
            'title' => 'انضم إلى بطولة FIFA واحتفل بالفوز',
            'badge' => 'بطولة الأسبوع',
            'body' => 'كل خميس مساءً — جوائز قيّمة للفائزين، تسجيل مفتوح لجميع اللاعبين. تحدَّ أصدقاءك واثبت من الأفضل!',
            'published_at' => now(),
            'is_published' => true,
        ]);
    }
}
