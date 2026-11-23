<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\EventPackage;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = [
            [
                'name' => 'النادي الاجتماعي — الصالة الكبرى',
                'location' => 'الطابق الأول، بجانب صالة السينما',
                'description' => 'صالة واسعة للجلسات العائلية والمناسبات الصغيرة، بإطلالة على الحديقة.',
                'color_from' => '#0f766e',
                'color_to' => '#22d3ee',
                'badge' => 'popular',
                'packages' => [
                    ['name' => 'جلسة عائلية', 'includes' => 'طاولة عائلية + مشروبات ترحيبية', 'people' => 4, 'start' => '16:00', 'end' => '19:00', 'price' => 60, 'default' => true],
                    ['name' => 'جلسة مميزة', 'includes' => 'طاولة VIP + ضيافة كاملة', 'people' => 6, 'start' => '19:00', 'end' => '23:00', 'price' => 120],
                ],
            ],
            [
                'name' => 'النادي الاجتماعي — الشرفة',
                'location' => 'الطابق الثاني، الشرفة الخارجية',
                'description' => 'جلسة هادئة في الهواء الطلق مع خدمة مخصصة.',
                'color_from' => '#4c1d95',
                'color_to' => '#a855f7',
                'badge' => 'new',
                'packages' => [
                    ['name' => 'جلسة ثنائية', 'includes' => 'طاولة لشخصين + مشروب لكل فرد', 'people' => 2, 'start' => '17:00', 'end' => '19:00', 'price' => 35, 'default' => true],
                    ['name' => 'جلسة مجموعة', 'includes' => 'طاولة كبيرة + ضيافة', 'people' => 8, 'start' => '20:00', 'end' => '23:00', 'price' => 140],
                ],
            ],
        ];

        foreach ($clubs as $index => $data) {
            $club = Club::create([
                'name' => $data['name'],
                'location' => $data['location'],
                'description' => $data['description'],
                'color_from' => $data['color_from'],
                'color_to' => $data['color_to'],
                'badge' => $data['badge'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);

            foreach ($data['packages'] as $i => $package) {
                $club->allPackages()->create([
                    'name' => $package['name'],
                    'includes' => $package['includes'],
                    'people_count' => $package['people'],
                    // Several tables share each time window, so the slot admits
                    // more guests than a single booking.
                    'capacity' => $package['people'] * 4,
                    'start_time' => $package['start'],
                    'end_time' => $package['end'],
                    'price' => $package['price'],
                    'is_default' => $package['default'] ?? false,
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]);
            }
        }

        $events = [
            ['name' => 'باقة أعياد الميلاد', 'includes' => 'طاولة مزينة + كيك + مشروبات', 'people' => 10, 'price' => 250, 'default' => true],
            ['name' => 'باقة المناسبات العائلية', 'includes' => 'قاعة خاصة + ضيافة كاملة', 'people' => 20, 'price' => 480],
            ['name' => 'باقة اجتماعات العمل', 'includes' => 'طاولة اجتماعات + بروجكتر + قهوة', 'people' => 12, 'price' => 300],
        ];

        foreach ($events as $i => $event) {
            EventPackage::create([
                'name' => $event['name'],
                'includes' => $event['includes'],
                'people_count' => $event['people'],
                'capacity' => $event['people'] * 2,
                'price' => $event['price'],
                'is_default' => $event['default'] ?? false,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }
}
