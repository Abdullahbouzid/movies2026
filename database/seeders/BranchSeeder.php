<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'المركز الرئيسي',
                'location' => 'شارع الجمهورية، وسط المدينة',
                'phone' => '0910000000',
                'services' => array_keys(Branch::SERVICES),
                'is_main' => true,
            ],
            [
                'name' => 'فرع الألعاب — المول',
                'location' => 'الطابق الثاني، مول المدينة',
                'phone' => '0910000001',
                'services' => ['games'],
                'is_main' => false,
            ],
            [
                'name' => 'فرع السينما — الساحل',
                'location' => 'طريق الساحل، مقابل الكورنيش',
                'phone' => '0910000002',
                'services' => ['cinema'],
                'is_main' => false,
            ],
        ];

        foreach ($branches as $i => $data) {
            Branch::updateOrCreate(
                ['name' => $data['name']],
                $data + ['sort_order' => $i + 1, 'is_active' => true],
            );
        }
    }
}
