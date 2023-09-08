<?php

declare(strict_types=1);

namespace Models;

use App\Enum\OrderStatus;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_user_can_be_created(): void
    {
        $data = [
            'name'              => 'Jane',
            'email'             => 'jane@example.com',
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => Str::random(10),
        ];

        $user = User::create($data);

        $this->assertDatabaseCount(User::class, 1);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($data['name'], $user->name);
        $this->assertSame($data['email'], $user->email);
    }

    public function test_an_user_can_be_created_using_a_factory(): void
    {
        User::factory()->create([
            'name'  => 'Jane',
            'email' => 'jane@example.com',
        ]);

        $user = User::first();

        $this->assertDatabaseCount(User::class, 1);
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_users_can_be_seeded(): void
    {
        $this->seed(UserSeeder::class);

        $users = User::all();
        $data  = UserSeeder::getData();
        $this->assertDatabaseCount(User::class, count($data));
        $this->assertInstanceOf(Collection::class, $users);

        $firstUser = $users->first();
        $this->assertInstanceOf(User::class, $firstUser);
        $this->assertSame($data['admin']['name'], $firstUser->name);
        $this->assertSame($data['admin']['email'], $firstUser->email);

        $user = $users->where('email', $data['user']['email'])->firstOrFail();
        $this->assertSame($data['user']['name'], $user->name);
        $this->assertSame($data['user']['email'], $user->email);
    }

    public function test_an_user_belongs_to_many_orders(): void
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

        $user = User::factory()->create();
        $this->assertInstanceOf(User::class, $user);

        $user->orders()->attach($order100, [
            'status' => OrderStatus::AVAILABLE,
        ]);
        $user->orders()->attach($order101, [
            'status' => OrderStatus::COMPLETE,
        ]);
        $user->orders()->attach($order102, [
            'status' => OrderStatus::IN_PROGRESS,
        ]);

        /** @var Collection<int, Order> $userOrders */
        $userOrders = $user->orders;
        $this->assertInstanceOf(Collection::class, $userOrders);
        $this->assertCount(3, $userOrders);

        $userWithCompleteStatus = $user->orders()
            ->wherePivot('status', OrderStatus::COMPLETE)
            ->get();
        $this->assertInstanceOf(Collection::class, $userWithCompleteStatus);
        $this->assertCount(1, $userWithCompleteStatus);

        $userWithCompleteStatus = $userWithCompleteStatus->first();
        $this->assertInstanceOf(Order::class, $userWithCompleteStatus);
        $this->assertSame($order101->name, $userWithCompleteStatus->name);
    }

    public function test_a_user_with_many_orders_can_update_the_status_of_one_order(): void
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

        /** @var Collection<int, User> $users */
        $users = User::factory(3)->create();
        $this->assertInstanceOf(Collection::class, $users);

        $users->each(static fn (User $user) => $user->orders()->attach($order101, [
            'status' => OrderStatus::IN_PROGRESS,
        ]));

        $middleUser = $users[1];
        $this->assertInstanceOf(User::class, $middleUser);

        $middleUser->orders()->updateExistingPivot($order101, [
            'status' => OrderStatus::COMPLETE,
        ]);

        /** @var Collection<int, Order> $completeUsersForOrder101 */
        $completeUsersForOrder101 = $middleUser->orders()
            ->wherePivot('status', OrderStatus::COMPLETE)
            ->wherePivot('order_id', $order101->id)
            ->get();

        $this->assertInstanceOf(Collection::class, $completeUsersForOrder101);
        $this->assertCount(1, $completeUsersForOrder101);

        $completeOrder = $completeUsersForOrder101->first();

        $this->assertInstanceOf(Order::class, $completeOrder);
        $this->assertSame($order101->name, $completeOrder->name);

        // last user is still IN_PROGRESS
        $lastUser = $users[2];
        $this->assertInstanceOf(User::class, $lastUser);

        /** @var Collection<int, Order> $lastUserOrders */
        $lastUserOrders = $lastUser->orders()->get();
        $this->assertCount(1, $lastUserOrders);

        $lastUserOrders->each(
            fn (Order $order) => $this->assertSame(OrderStatus::IN_PROGRESS->getLabel(), $order->pivot->status)
        );
    }

    public function test_an_user_can_have_many_deliveries(): void
    {
        User::factory()->create();
        $user = User::factory()->create();
        User::factory()->create();

        $this->assertInstanceOf(User::class, $user);

        $deliveries = Delivery::factory(3)->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(Collection::class, $deliveries);

        /** @var Collection<int, Delivery> $userDeliveries */
        $userDeliveries = $user->deliveries;
        $this->assertInstanceOf(Collection::class, $userDeliveries);
        $this->assertCount(3, $userDeliveries);

        $userDeliveries->each(fn (Delivery $delivery) => $this->assertSame($user->id, $delivery->user_id));

        $firstOrderDelivery = $userDeliveries->first();
        $firstDelivery      = $deliveries->first();

        $this->assertInstanceOf(Delivery::class, $firstOrderDelivery);
        $this->assertInstanceOf(Delivery::class, $firstDelivery);
        $this->assertSame($firstDelivery->user_id, $firstOrderDelivery->user_id);
    }
}
