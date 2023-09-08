<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\District;
use App\Models\Location;
use App\Models\Order;
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

        $location = Location::create($data);

        $this->assertDatabaseCount(Location::class, 1);
        $this->assertDatabaseHas(Location::class, $data);
        $this->assertInstanceOf(Location::class, $location);
        $this->assertSame($data['name'], $location->name);
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

    public function test_a_location_can_be_limited_to_physical_location(): void
    {
        $district = District::factory()->create();
        $this->assertInstanceOf(District::class, $district);

        Location::factory()->create([
            'name'        => 'Other',
            'district_id' => $district->id,
            'is_physical' => false,
        ]);

        $physicalLocation = Location::factory()->create([
            'name'        => 'Capital Knot City',
            'district_id' => $district->id,
            'is_physical' => true,
        ]);

        Location::factory()->create([
            'name'        => 'In progress',
            'district_id' => $district->id,
            'is_physical' => false,
        ]);

        $onlyPhysicalLocation = Location::query()
            ->isPhysical()
            ->first();

        $this->assertInstanceOf(Location::class, $physicalLocation);
        $this->assertInstanceOf(Location::class, $onlyPhysicalLocation);
        $this->assertSame($physicalLocation->name, $onlyPhysicalLocation->name);
    }

    public function test_a_location_can_have_many_client_orders(): void
    {
        Location::factory()->create([
            'name' => 'Other',
        ]);

        $location = Location::factory()->create([
            'name' => 'Capital Knot City',
        ]);

        Location::factory()->create([
            'name' => 'Distribution Center South of Lake Knot City',
        ]);

        /** @var Collection<int, Order> $orders */
        $orders = Order::factory(3)->create([
            'client_id' => $location,
        ]);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertInstanceOf(Collection::class, $orders);

        $clientOrders = $location->clientOrders;
        $this->assertInstanceOf(Collection::class, $orders);
        $this->assertCount(3, $clientOrders);

        $clientOrders->each(fn (Order $order) => $this->assertSame($location->id, $order->client_id));
    }

    public function test_a_location_can_have_many_destination_orders(): void
    {
        Location::factory()->create([
            'name' => 'Other',
        ]);

        $location = Location::factory()->create([
            'name' => 'Distribution Center South of Lake Knot City',
        ]);

        Location::factory()->create([
            'name' => 'Capital Knot City',
        ]);

        /** @var Collection<int, Order> $orders */
        $orders = Order::factory(3)->create([
            'client_id' => $location,
        ]);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertInstanceOf(Collection::class, $orders);

        $clientOrders = $location->clientOrders;
        $this->assertInstanceOf(Collection::class, $orders);
        $this->assertCount(3, $clientOrders);

        $clientOrders->each(fn (Order $order) => $this->assertSame($location->id, $order->client_id));

        $firstClientOrder = $clientOrders->first();
        $firstOrder       = $orders->first();

        $this->assertInstanceOf(Order::class, $firstClientOrder);
        $this->assertInstanceOf(Order::class, $firstOrder);
        $this->assertSame($firstOrder->name, $firstClientOrder->name);
    }
}
