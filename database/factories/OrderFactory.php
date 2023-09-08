<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeliveryCategory;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number'               => fake()->numberBetween(100, 540),
            'name'                 => fake()->sentence(),
            'client_id'            => Location::factory(),
            'destination_id'       => Location::factory(),
            'delivery_category_id' => DeliveryCategory::factory(),
            'max_likes'            => fake()->numberBetween(20, 200),
            'weight'               => fake()->numberBetween(50, 25200) / 10,
        ];
    }
}
