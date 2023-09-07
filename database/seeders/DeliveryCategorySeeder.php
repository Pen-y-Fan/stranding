<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DeliveryCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeliveryCategory::insertOrIgnore(self::getData());
    }

    public static function getData(): array
    {
        return [
            'delivery time' => [
                'name'       => 'Delivery Time',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            'delivery volume' => [
                'name'       => 'Delivery Volume',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            'cargo condition' => [
                'name'       => 'Cargo Condition',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            'miscellaneous' => [
                'name'       => 'Miscellaneous',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
