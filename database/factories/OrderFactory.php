<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
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
            'user_id' => User::factory(),
            'payment_method' => fake()->randomElement([
                PaymentMethod::CREDIT_CARD,
                PaymentMethod::DEBIT_CARD,
                PaymentMethod::PIX,
            ]),
            'total_cents' => fake()->numberBetween(100, 100_000),
            'amount_received_cents' => null,
        ];
    }

    /**
     * Indicate that the order was paid in cash, with change when the received amount exceeds the total.
     */
    public function paidInCash(?int $amountReceivedCents = null): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::CASH,
            'amount_received_cents' => $amountReceivedCents
                ?? $attributes['total_cents'] + fake()->numberBetween(0, 5_000),
        ]);
    }
}
