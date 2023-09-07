<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        District::insertOrIgnore(self::getData());
    }

    public static function getData(): array
    {
        return [
            'west' => [
                'name'       => 'West',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            'central' => [
                'name'       => 'Central',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            'east' => [
                'name'       => 'East',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
