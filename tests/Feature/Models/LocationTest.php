<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\District;
use App\Models\Location;
use Database\Seeders\DistrictSeeder;
use Database\Seeders\LocationSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_location_can_be_created(): void
    {
        $district = District::factory()->create();
        $this->assertInstanceOf(District::class, $district);

        $data = [
            'name'        => 'Capital Knot City',
            'district_id' => $district->id,
        ];

        $district = Location::create($data);

        $this->assertDatabaseCount(Location::class, 1);
        $this->assertDatabaseHas(Location::class, $data);
        $this->assertInstanceOf(Location::class, $district);
        $this->assertSame($data['name'], $district->name);
    }

    public function test_a_location_can_be_created_using_a_factory(): void
    {
        $location = Location::factory()->create();

        $this->assertDatabaseCount(Location::class, 1);
        $this->assertInstanceOf(Location::class, $location);
    }

    public function test_locations_can_be_seeded(): void
    {
        $this->seed(DistrictSeeder::class);
        $this->seed(LocationSeeder::class);

        $locations = Location::all();
        $data      = LocationSeeder::getData();
        $this->assertDatabaseCount(Location::class, count($data));
        $this->assertInstanceOf(Collection::class, $locations);
        $firstLocation = $locations->first();
        $this->assertInstanceOf(Location::class, $firstLocation);
        $this->assertSame($data['Capital Knot City']['name'], $firstLocation->name);
    }

    public function test_a_location_belong_to_a_district(): void
    {
        $district = District::factory()->create();
        $this->assertInstanceOf(District::class, $district);

        $location = Location::factory()->create([
            'district_id' => $district->id,
        ]);
        $this->assertInstanceOf(Location::class, $location);

        $locationDistrict = $location->district;
        $this->assertInstanceOf(District::class, $locationDistrict);

        $this->assertSame($district->id, $locationDistrict->id);
        $this->assertSame($district->name, $locationDistrict->name);
    }
}
