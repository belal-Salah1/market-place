<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->text,
            'price' => $this->faker->numberBetween(1, 1000),
            'stock' => $this->faker->numberBetween(1, 100),
            'image' => $this->faker->image,
            'category_id' => Category::factory(),
            'vendor_id' => User::factory(),
        ];
    }
}
