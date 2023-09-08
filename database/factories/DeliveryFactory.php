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
    private const STATUS = [
        DeliveryStatus::IN_PROGRESS,
        DeliveryStatus::FAILED,
        DeliveryStatus::COMPLETE,
        DeliveryStatus::STASHED,
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(self::STATUS);
        assert($status instanceof DeliveryStatus);

        $startedAt = now()->startOfDay()->subDays(random_int(1, 21));
        $endedAt   = $status === DeliveryStatus::FAILED || $status === DeliveryStatus::COMPLETE ? $startedAt->clone()->addDay() : null;

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
