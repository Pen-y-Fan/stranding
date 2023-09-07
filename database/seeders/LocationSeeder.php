<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\District;
use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::insertOrIgnore(self::getData());
    }

    public static function getData(): array
    {
        $districts = self::getDistricts();

        $west    = $districts->where('name', 'West')->first();
        $central = $districts->where('name', 'Central')->first();
        // $eastId    = $districts->where('name', 'East')->first()->id;
        assert($west instanceof District);
        assert($central instanceof District);

        return [
            'Capital Knot City' => [
                'name'        => 'Capital Knot City',
                'district_id' => $west->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],

            "Chiral Artist's Studio" => [
                'name'        => "Chiral Artist's Studio",
                'district_id' => $central->id,

                'created_at' => now(),
                'updated_at' => now(),
            ],
            'Collector' => [
                'name'        => 'Collector',
                'district_id' => $central->id,

                'created_at' => now(),
                'updated_at' => now(),
            ],
            'Cosplayer' => [
                'name'        => 'Cosplayer',
                'district_id' => $central->id,

                'created_at' => now(),
                'updated_at' => now(),
            ],
            'Craftsman' => [
                'name'        => 'Craftsman',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Distribution Center North of Mountain Knot City' => [
                'name'        => 'Distribution Center North of Mountain Knot City',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Distribution Center South of Lake Knot City' => [
                'name'        => 'Distribution Center South of Lake Knot City',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Distribution Center West of Capital Knot City' => [
                'name'        => 'Distribution Center West of Capital Knot City',
                'district_id' => $west->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Doctor' => [
                'name'        => 'Doctor',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Elder' => [
                'name'        => 'Elder',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Engineer' => [
                'name'        => 'Engineer',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Evo-devo Biologist' => [
                'name'        => 'Evo-devo Biologist',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Film Director' => [
                'name'        => 'Film Director',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'First Prepper' => [
                'name'        => 'First Prepper',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Geologist' => [
                'name'        => 'Geologist',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            "Heartman's Lab" => [
                'name'        => "Heartman's Lab",
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Junk Dealer' => [
                'name'        => 'Junk Dealer',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Lake Knot City' => [
                'name'        => 'Lake Knot City',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Ludens Fan' => [
                'name'        => 'Ludens Fan',
                'district_id' => $west->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            "Mama's Lab" => [
                'name'        => "Mama's Lab",
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Mountain Knot City' => [
                'name'        => 'Mountain Knot City',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Mountaineer' => [
                'name'        => 'Mountaineer',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Musician' => [
                'name'        => 'Musician',
                'district_id' => $west->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            "Novelist's Son" => [
                'name'        => "Novelist's Son",
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Paleontologist' => [
                'name'        => 'Paleontologist',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Photographer' => [
                'name'        => 'Photographer',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Port Knot City' => [
                'name'        => 'Port Knot City',
                'district_id' => $west->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Roboticist' => [
                'name'        => 'Roboticist',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'South Knot City' => [
                'name'        => 'South Knot City',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Spiritualist' => [
                'name'        => 'Spiritualist',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Timefall Farm' => [
                'name'        => 'Timefall Farm',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Veteran Porter' => [
                'name'        => 'Veteran Porter',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Waystation North of Mountain Knot City' => [
                'name'        => 'Waystation North of Mountain Knot City',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Waystation West of Capital Knot City' => [
                'name'        => 'Waystation West of Capital Knot City',
                'district_id' => $west->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Weather Station' => [
                'name'        => 'Weather Station',
                'district_id' => $central->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            'Wind Farm' => [
                'name'        => 'Wind Farm',
                'district_id' => $west->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];
    }

    /**
     * @return Collection<int, District>
     */
    private static function getDistricts(): Collection
    {
        $districts = District::all();

        abort_if($districts->count() === 0, 404, 'District must be seeded prior to location');

        return $districts;
    }
}
