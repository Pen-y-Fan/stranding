<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enum\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Location;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use function PHPUnit\Framework\assertSame;

class DeliverySeeder extends Seeder
{
    use WithoutModelEvents;

    private const STATUS = [
        DeliveryStatus::IN_PROGRESS,
        DeliveryStatus::FAILED,
        DeliveryStatus::COMPLETE,
        DeliveryStatus::STASHED,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Delivery::insertOrIgnore(self::getData());
    }

    /**
     * @throws \Exception
     */
    public static function getData(): array
    {
        $locations          = self::getLocations();
        $users              = self::getUsers();
        $orders             = self::getOrders();
        $inProgressLocation = $locations['In progress (Central)'];

        $deliveries = [];

        for ($i = 0; $i < 20; ++$i) {
            $status = fake()->randomElement(self::STATUS);
            assert($status instanceof DeliveryStatus);

            $startedAt = now()->startOfDay()->subDays(random_int(1, 21));
            $endedAt   = $status === DeliveryStatus::FAILED || $status === DeliveryStatus::COMPLETE ? $startedAt->clone()->addDay() : null;
            $order     = fake()->randomElement($orders);
            $user      = fake()->randomElement($users);
            $location  = $status === DeliveryStatus::IN_PROGRESS ? $inProgressLocation : fake()->randomElement($locations);
            assert($order instanceof Order);
            assert($user instanceof User);
            assert($location instanceof Location);
            $deliveries[] = [
                'started_at'  => $startedAt,
                'ended_at'    => $endedAt,
                'status'      => $status,
                'order_id'    => $order->id,
                'user_id'     => $user->id,
                'location_id' => $location->id,
                'comment'     => fake()->sentence(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        return $deliveries;
    }

    /**
     * @return Collection<string, Location>
     */
    private static function getLocations(): Collection
    {
        $locations = Location::all();

        abort_if($locations->count() === 0, 404, 'Locations must be seeded prior to Deliveries');

        return $locations->keyBy('name');
    }

    /**
     * @return Collection<string, User>
     */
    private static function getUsers(): Collection
    {
        $users = User::all();

        abort_if($users->count() === 0, 404, 'Users must be seeded prior to Deliveries');

        return $users->keyBy('name');
    }

    /**
     * @return Collection<string, Order>
     */
    private static function getOrders(): Collection
    {
        $orders = Order::all();

        abort_if($orders->count() === 0, 404, 'Orders must be seeded prior to Deliveries');

        return $orders->keyBy('name');
    }
}
