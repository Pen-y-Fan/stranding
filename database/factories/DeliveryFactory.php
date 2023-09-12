<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\DeliveryStatus;
use App\Models\Location;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Delivery>
 */
class DeliveryFactory extends Factory
{
    //    private const STATUS = DeliveryStatus::toArrary;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function definition(): array
    {
        $status = fake()->randomElement(DeliveryStatus::toArrayEnum());
        assert($status instanceof DeliveryStatus);

        $startedAt = now()->startOfDay()->subDays(random_int(1, 21));
        $endedAt   = $status === DeliveryStatus::IN_PROGRESS || $status === DeliveryStatus::STASHED
            ? null
            : $startedAt->clone()->addDay();

        return [
            'started_at'  => $startedAt,
            'ended_at'    => $endedAt,
            'status'      => $status,
            'order_id'    => Order::factory(),
            'user_id'     => User::factory(),
            'location_id' => Location::factory(),
            'comment'     => fake()->sentence(),
        ];
    }
}
