<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\District;
use App\Models\Location;
use Database\Seeders\DistrictSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DistrictTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_district_can_be_created(): void
    {
        $data = [
            'name' => 'West',
        ];
        $district = District::create($data);

        $this->assertDatabaseCount(District::class, 1);
        $this->assertDatabaseHas(District::class, $data);
        $this->assertInstanceOf(District::class, $district);
        $this->assertSame($data['name'], $district->name);
    }

    public function test_a_district_can_be_created_using_a_factory(): void
    {
        $district = District::factory()->create();

        $this->assertDatabaseCount(District::class, 1);
        $this->assertInstanceOf(District::class, $district);
    }

    public function test_districts_can_be_seeded(): void
    {
        $this->seed(DistrictSeeder::class);

        $districts = District::all();
        $data      = DistrictSeeder::getData();
        $this->assertDatabaseCount(District::class, count($data));
        $this->assertInstanceOf(Collection::class, $districts);
        $firstDistrict = $districts->first();
        $this->assertInstanceOf(District::class, $firstDistrict);
        $this->assertSame($data['west']['name'], $firstDistrict->name);
    }

    public function test_a_district_can_have_many_locations(): void
    {
        $locationQuantity = 3;
        $district         = District::factory()->create();
        $this->assertInstanceOf(District::class, $district);

        $locations = Location::factory($locationQuantity)->create([
            'district_id' => $district->id,
        ]);
        $this->assertInstanceOf(Collection::class, $locations);

        $districtLocations = $district->locations;
        $this->assertInstanceOf(Collection::class, $districtLocations);
        $this->assertCount($locationQuantity, $districtLocations);

        $firstDistrictLocation = $districtLocations->first();
        $firstLocation         = $locations->first();

        $this->assertInstanceOf(Location::class, $firstDistrictLocation);
        $this->assertInstanceOf(Location::class, $firstLocation);
        $this->assertSame($firstDistrictLocation->name, $firstLocation->name);
    }
}
