<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enum\OrderStatus;
use App\Models\Delivery;
use App\Models\DeliveryCategory;
use App\Models\District;
use App\Models\Location;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DeliveryCategorySeeder;
use Database\Seeders\DistrictSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\OrderSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_order_can_be_created(): void
    {
        // No. 100,	[URGENT] Delivery: Tranquilizers,	Capital Knot City,	Waystation West of Capital Knot City,
        // Delivery Time,	≈45,	2.4 kg
        $clientLocation = Location::factory()->create([
            'name' => 'Capital Knot City',
        ]);

        $destinationLocation = Location::factory()->create([
            'name' => 'Waystation West of Capital Knot City',
        ]);

        $deliveryCategory = DeliveryCategory::factory()->create([
            'name' => 'Delivery Time',
        ]);

        $data = [
            'number'               => 100,
            'name'                 => '[URGENT] Delivery: Tranquilizers',
            'client_id'            => $clientLocation->id,
            'destination_id'       => $destinationLocation->id,
            'delivery_category_id' => $deliveryCategory->id,
            'max_likes'            => 45,
            'weight'               => 2.4,
        ];

        $order = Order::create($data);

        $this->assertDatabaseCount(Order::class, 1);
        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame($data['name'], $order->name);

        $this->assertSame((int) ($data['weight'] * 1000), (int) ($order->weight * 1000));
    }

    public function test_an_order_can_be_created_using_a_factory(): void
    {
        Order::factory()->create();

        $order = Order::first();

        $this->assertDatabaseCount(Order::class, 1);
        $this->assertInstanceOf(Order::class, $order);
    }

    public function test_orders_can_be_seeded(): void
    {
        $this->seed(DistrictSeeder::class);
        $this->seed(LocationSeeder::class);
        $this->seed(DeliveryCategorySeeder::class);
        $this->seed(OrderSeeder::class);

        $orders = Order::all();
        $data   = OrderSeeder::getData();
        $this->assertDatabaseCount(Order::class, count($data));
        $this->assertInstanceOf(Collection::class, $orders);

        $firstOrder = $orders->first();
        $this->assertInstanceOf(Order::class, $firstOrder);
        $this->assertSame($data[100]['name'], $firstOrder->name);

        $order164 = $orders->where('number', 164)->firstOrFail();
        $this->assertSame($data[164]['name'], $order164->name);
        $order639 = $orders->where('number', 639)->firstOrFail();
        $this->assertSame($data[639]['name'], $order639->name);
    }

    public function test_an_order_belongs_to_a_client_location_and_destination_location(): void
    {
        $clientLocation = Location::factory()->create([
            'name' => 'Capital Knot City',
        ]);
        $this->assertInstanceOf(Location::class, $clientLocation);

        $destinationLocation = Location::factory()->create([
            'name' => 'Mountain Knot City',
        ]);
        $this->assertInstanceOf(Location::class, $destinationLocation);
        $order = Order::factory()->create([
            'client_id'      => $clientLocation->id,
            'destination_id' => $destinationLocation->id,
        ]);
        $this->assertInstanceOf(Order::class, $order);

        $orderClient = $order->client;
        $this->assertInstanceOf(Location::class, $orderClient);

        $orderDestination = $order->destination;
        $this->assertInstanceOf(Location::class, $orderDestination);

        $this->assertSame($clientLocation->id, $orderClient->id);
        $this->assertSame($clientLocation->name, $orderClient->name);

        $this->assertSame($destinationLocation->id, $orderDestination->id);
        $this->assertSame($destinationLocation->name, $orderDestination->name);
    }

    public function test_an_order_belongs_to_a_district_via_the_client_location(): void
    {
        District::factory()->create();
        $district = District::factory()->create([
            'name' => 'Central',
        ]);
        District::factory()->create();

        $this->assertInstanceOf(District::class, $district);

        $location = Location::factory()->create([
            'name'        => 'South Knot City',
            'district_id' => $district,
        ]);
        $this->assertInstanceOf(Location::class, $location);

        $order = Order::factory()->create([
            'client_id' => $location,
        ]);
        $this->assertInstanceOf(Order::class, $order);

        $orderClientDistrict = $order->client->district;
        $this->assertInstanceOf(District::class, $orderClientDistrict);
        $this->assertSame($district->name, $orderClientDistrict->name);
    }

    public function test_an_order_belongs_to_a_delivery_category(): void
    {
        DeliveryCategory::factory()->create();
        $deliveryCategory = DeliveryCategory::factory()->create([
            'name' => 'Delivery Volume',
        ]);
        DeliveryCategory::factory()->create();
        $this->assertInstanceOf(DeliveryCategory::class, $deliveryCategory);

        $order = Order::factory()->create([
            'delivery_category_id' => $deliveryCategory,
        ]);
        $this->assertInstanceOf(Order::class, $order);

        $orderDeliveryCategory = $order->deliveryCategory;
        $this->assertInstanceOf(DeliveryCategory::class, $orderDeliveryCategory);
        $this->assertSame($deliveryCategory->name, $orderDeliveryCategory->name);
    }

    public function test_an_order_belongs_to_many_users(): void
    {
        $fred = User::factory()->create([
            'name' => 'Fred',
        ]);
        $jill = User::factory()->create([
            'name' => 'Jill',
        ]);
        $jack = User::factory()->create([
            'name' => 'Jack',
        ]);
        $this->assertInstanceOf(User::class, $fred);
        $this->assertInstanceOf(User::class, $jill);
        $this->assertInstanceOf(User::class, $jack);

        $order = Order::factory()->create();
        $this->assertInstanceOf(Order::class, $order);

        $order->users()->attach($fred, [
            'status' => OrderStatus::AVAILABLE,
        ]);
        $order->users()->attach($jill, [
            'status' => OrderStatus::COMPLETE,
        ]);
        $order->users()->attach($jack, [
            'status' => OrderStatus::IN_PROGRESS,
        ]);

        /** @var Collection<int, User> $orderUsers */
        $orderUsers = $order->users;
        $this->assertInstanceOf(Collection::class, $orderUsers);
        $this->assertCount(3, $orderUsers);

        $userWithCompleteStatus = $order->users()
            ->wherePivot('status', OrderStatus::COMPLETE)
            ->get();
        $this->assertInstanceOf(Collection::class, $userWithCompleteStatus);
        $this->assertCount(1, $userWithCompleteStatus);

        $userWithCompleteStatus = $userWithCompleteStatus->first();
        $this->assertInstanceOf(User::class, $userWithCompleteStatus);
        $this->assertSame($jill->name, $userWithCompleteStatus->name);
    }

    public function test_a_user_with_many_orders_can_update_the_status_of_one_order(): void
    {
        $jill = User::factory()->create([
            'name' => 'Jill',
        ]);
        $jack = User::factory()->create([
            'name' => 'Jack',
        ]);
        $this->assertInstanceOf(User::class, $jill);
        $this->assertInstanceOf(User::class, $jack);

        /** @var Collection<int, Order> $orders */
        $orders = Order::factory(3)->create();
        $this->assertInstanceOf(Collection::class, $orders);

        $orders->each(static fn (Order $order) => $order->users()->attach($jill, [
            'status' => OrderStatus::IN_PROGRESS,
        ]));

        $middleOrder = $orders[1];
        $this->assertInstanceOf(Order::class, $middleOrder);

        $middleOrder->users()->updateExistingPivot($jill, [
            'status' => OrderStatus::COMPLETE,
        ]);

        $completeOrdersForJill = $middleOrder->users()
            ->wherePivot('status', OrderStatus::COMPLETE)
            ->wherePivot('user_id', $jill->id)
            ->get();

        $this->assertInstanceOf(Collection::class, $completeOrdersForJill);

        $jillOrder = $completeOrdersForJill->first();

        $this->assertInstanceOf(User::class, $jillOrder);
        $this->assertSame($jill->name, $jillOrder->name);

        // last order is still IN_PROGRESS
        $lastOrder = $orders[2];
        $this->assertInstanceOf(Order::class, $lastOrder);

        /** @var Collection<int, User> $lastOrderUsers */
        $lastOrderUsers = $lastOrder->users()->get();
        $this->assertCount(1, $lastOrderUsers);

        $lastOrderUsers->each(fn (User $user) => $this->assertSame(OrderStatus::IN_PROGRESS->getLabel(), $user->pivot->status));
    }

    public function test_an_order_can_have_many_deliveries(): void
    {
        $order100 = Order::factory()->create([
            'number' => 100,
            'name'   => '[URGENT] Delivery: Tranquilizers',
        ]);
        $order101 = Order::factory()->create([
            'number' => 101,
            'name'   => 'Materials Delivery: Metals & Ceramics',
        ]);
        $order102 = Order::factory()->create([
            'number' => 102,
            'name'   => '[Re-order] Delivery: Smart Drugs to Waystation West of Capital Knot City',
        ]);
        $this->assertInstanceOf(Order::class, $order100);
        $this->assertInstanceOf(Order::class, $order101);
        $this->assertInstanceOf(Order::class, $order102);

        /** @var Collection<int, Delivery> $deliveries */
        $deliveries = Delivery::factory(3)->create([
            'order_id' => $order101->id,
        ]);

        $this->assertInstanceOf(Collection::class, $deliveries);

        /** @var Collection<int, Delivery> $orderDeliveries */
        $orderDeliveries = $order101->deliveries;
        $this->assertInstanceOf(Collection::class, $deliveries);
        $this->assertCount(3, $orderDeliveries);

        $orderDeliveries->each(fn (Delivery $delivery) => $this->assertSame($order101->id, $delivery->order_id));

        $firstOrderDelivery = $orderDeliveries->first();
        $firstDelivery      = $deliveries->first();

        $this->assertInstanceOf(Delivery::class, $firstOrderDelivery);
        $this->assertInstanceOf(Delivery::class, $firstDelivery);
        $this->assertSame($firstDelivery->order_id, $firstOrderDelivery->order_id);
    }
}
