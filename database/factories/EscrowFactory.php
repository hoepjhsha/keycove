<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Escrow;
use App\Models\Order;
use App\Enums\EscrowStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Escrow>
 */
class EscrowFactory extends Factory
{
    protected $model = Escrow::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => fake()->randomFloat(2, 9.99, 299.99),
            'release_date' => fake()->dateTimeBetween('+3 days', '+14 days'),
            'status' => EscrowStatus::Holding,
        ];
    }

    public function forOrder(?Order $order = null): static
    {
        return $this->state(function (array $attributes) use ($order) {
            $orderEntity = $order ?? Order::factory()->create();

            return [
                'order_id' => $orderEntity->id,
                'amount' => $orderEntity->total_price,
            ];
        });
    }

    public function holding(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EscrowStatus::Holding,
        ]);
    }

    public function released(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EscrowStatus::Released,
            'release_date' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EscrowStatus::Refunded,
        ]);
    }
}
