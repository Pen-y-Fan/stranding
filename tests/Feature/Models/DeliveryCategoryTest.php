<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\DeliveryCategory;
use App\Models\Order;
use Database\Seeders\DeliveryCategorySeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_delivery_category_can_be_created(): void
    {
        $data = [
            'name' => 'Delivery Time',
        ];
        $deliveryCategory = DeliveryCategory::create($data);

        $this->assertDatabaseCount(DeliveryCategory::class, 1);
        $this->assertDatabaseHas(DeliveryCategory::class, $data);
        $this->assertInstanceOf(DeliveryCategory::class, $deliveryCategory);
        $this->assertSame($data['name'], $deliveryCategory->name);
    }

    public function test_a_delivery_category_can_be_created_using_a_factory(): void
    {
        $deliveryCategory = DeliveryCategory::factory()->create();

        $this->assertDatabaseCount(DeliveryCategory::class, 1);
        $this->assertInstanceOf(DeliveryCategory::class, $deliveryCategory);
    }

    public function test_delivery_categories_can_be_seeded(): void
    {
        $this->seed(DeliveryCategorySeeder::class);

        $deliveryCategories = DeliveryCategory::all();
        $data               = DeliveryCategorySeeder::getData();
        $this->assertDatabaseCount(DeliveryCategory::class, count($data));
        $this->assertInstanceOf(Collection::class, $deliveryCategories);
        $firstDeliveryCategory = $deliveryCategories->first();
        $this->assertInstanceOf(DeliveryCategory::class, $firstDeliveryCategory);
        $this->assertSame($data['delivery time']['name'], $firstDeliveryCategory->name);
    }

    public function test_a_delivery_category_can_have_many_orders(): void
    {
        $orderQuantity    = 3;
        $deliveryCategory = DeliveryCategory::factory()->create();
        $this->assertInstanceOf(DeliveryCategory::class, $deliveryCategory);

        Order::factory()->create();
        /** @var Collection<int, Order> $orders */
        $orders = Order::factory($orderQuantity)->create([
            'delivery_category_id' => $deliveryCategory->id,
        ]);
        Order::factory()->create();
        $this->assertInstanceOf(Collection::class, $orders);

        $deliveryCategoryOrders = $deliveryCategory->orders;
        $this->assertInstanceOf(Collection::class, $deliveryCategoryOrders);
        $this->assertCount($orderQuantity, $deliveryCategoryOrders);

        $firstDeliveryCategoryOrder = $deliveryCategoryOrders->first();
        $firstOrder                 = $orders->first();

        $this->assertInstanceOf(Order::class, $firstDeliveryCategoryOrder);
        $this->assertInstanceOf(Order::class, $firstOrder);
        $this->assertSame($firstDeliveryCategoryOrder->name, $firstOrder->name);
    }
}
