<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
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
            'code' => fake()->unique()->ean13(),
            'name' => ucfirst(fake()->word().' '.fake()->word()),
            'price_cents' => fake()->numberBetween(100, 50_000),
            'stock' => fake()->numberBetween(10, 200),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the product is no longer available for sale.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product has no stock left.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
