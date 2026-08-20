<?php

namespace Database\Factories;

use App\Enums\PaymentMethodStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => fake()->numberBetween(1, 1000),
            'method' => fake()->randomElement(PaymentMethodStatus::cases()),
            'status' => fake()->randomElement(PaymentStatus::cases()),
        ];
    }
}
