<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * The product name and unit price are snapshotted from the related product.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fn (array $attributes): string => Product::query()->whereKey($attributes['product_id'])->firstOrFail()->name,
            'unit_price_cents' => fn (array $attributes): int => Product::query()->whereKey($attributes['product_id'])->firstOrFail()->price_cents,
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }
}
