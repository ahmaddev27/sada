<?php

// BIL-01: seed default token packages

namespace Database\Seeders;

use App\Models\TokenPackage;
use Illuminate\Database\Seeder;

class TokenPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name'       => 'باقة المبتدئ',
                'name_en'    => 'Starter',
                'tokens'     => 500,
                'price'      => 49.00,
                'is_popular' => false,
                'sort_order' => 1,
            ],
            [
                'name'       => 'باقة الأعمال',
                'name_en'    => 'Business',
                'tokens'     => 1500,
                'price'      => 129.00,
                'is_popular' => true,
                'sort_order' => 2,
            ],
            [
                'name'       => 'باقة النمو',
                'name_en'    => 'Growth',
                'tokens'     => 3500,
                'price'      => 269.00,
                'is_popular' => false,
                'sort_order' => 3,
            ],
            [
                'name'       => 'باقة الوكالة',
                'name_en'    => 'Agency',
                'tokens'     => 10000,
                'price'      => 699.00,
                'is_popular' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($packages as $package) {
            TokenPackage::firstOrCreate(
                ['name_en' => $package['name_en']],
                $package,
            );
        }
    }
}
