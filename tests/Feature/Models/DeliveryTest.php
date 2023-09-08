<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enum\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Location;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DeliveryCategorySeeder;
use Database\Seeders\DeliverySeeder;
use Database\Seeders\DistrictSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_delivery_can_be_created(): void
    {
        $freda = User::factory()->create([
            'name' => 'Freda',
        ]);

        $order = Order::factory()->create([
            'number' => 100,
            'name'   => '[URGENT] Delivery: Tranquilizers',
        ]);

        $location = Location::factory()->create([
            'name'        => 'In progress (West)',
            'is_physical' => false,
        ]);

        $this->assertInstanceOf(User::class, $freda);
        $this->assertInstanceOf(Order::class, $order);
        $this->assertInstanceOf(Location::class, $location);

        $data = [
            'order_id'    => $order->id,
            'user_id'     => $freda->id,
            'started_at'  => now(),
            'ended_at'    => null,
            'status'      => DeliveryStatus::IN_PROGRESS,
            'location_id' => $location->id,
            'comment'     => "I'm going for it!",
        ];

        $delivery = Delivery::create($data);

        $this->assertDatabaseCount(Delivery::class, 1);
        $this->assertDatabaseHas(Delivery::class, $data);
        $this->assertInstanceOf(Delivery::class, $delivery);
        $this->assertSame($data['user_id'], $delivery->user_id);
    }

    public function test_a_delivery_can_be_created_using_a_factory(): void
    {
        $delivery = Delivery::factory()->create();

        $this->assertDatabaseCount(Delivery::class, 1);
        $this->assertInstanceOf(Delivery::class, $delivery);
    }

    /**
     * @throws \Exception
     */
    public function test_deliveries_can_be_seeded(): void
    {
        $this->seed(DistrictSeeder::class);
        $this->seed(LocationSeeder::class);
        $this->seed(DeliveryCategorySeeder::class);
        $this->seed(OrderSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(DeliverySeeder::class);

        $deliveries = Delivery::all();
        $data       = DeliverySeeder::getData();
        $this->assertDatabaseCount(Delivery::class, count($data));
        $this->assertInstanceOf(Collection::class, $deliveries);
        $firstDelivery = $deliveries->first();
        $this->assertInstanceOf(Delivery::class, $firstDelivery);
    }

    public function test_a_delivery_belongs_to_a_location(): void
    {
        $location = Location::factory()->create([
            'name' => 'Capital Knot City',
        ]);
        $this->assertInstanceOf(Location::class, $location);

        $delivery = Delivery::factory()->create([
            'location_id' => $location->id,
        ]);
        $this->assertInstanceOf(Delivery::class, $delivery);

        $deliveryLocation = $delivery->location;
        $this->assertInstanceOf(Location::class, $deliveryLocation);
        $this->assertSame($location->name, $deliveryLocation->name);
    }

    //order
    public function test_a_delivery_belongs_to_an_order(): void
    {
        $order = Order::factory()->create([
            'number' => 100,
            'name'   => '[URGENT] Delivery: Tranquilizers',

        ]);
        $this->assertInstanceOf(Order::class, $order);

        $delivery = Delivery::factory()->create([
            'order_id' => $order->id,
        ]);
        $this->assertInstanceOf(Delivery::class, $delivery);

        $deliveryOrder = $delivery->order;
        $this->assertInstanceOf(Order::class, $deliveryOrder);
        $this->assertSame($order->name, $deliveryOrder->name);
    }

    public function test_a_delivery_belongs_to_a_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Fred',

        ]);
        $this->assertInstanceOf(User::class, $user);

        $delivery = Delivery::factory()->create([
            'user_id' => $user->id,
        ]);
        $this->assertInstanceOf(Delivery::class, $delivery);

        $deliveryUser = $delivery->user;
        $this->assertInstanceOf(User::class, $deliveryUser);
        $this->assertSame($user->name, $deliveryUser->name);
    }
}
