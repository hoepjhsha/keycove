<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EscrowStatus;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
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
            'order_item_id' => OrderItem::factory(),
            'seller_id'     => Seller::factory(),
            'amount'        => fake()->randomFloat(2, 9.99, 299.99),
            'release_date'  => fake()->dateTimeBetween('+3 days', '+14 days'),
            'status'        => EscrowStatus::Holding,
        ];
    }

    public function forOrder(?Order $order = null): static
    {
        return $this->state(function (array $attributes) use ($order) {
            $orderEntity = $order ?? Order::factory()->has(OrderItem::factory(), 'items')->create();
            $orderItem = $orderEntity->items()->first() ?? OrderItem::factory()->forOrder($orderEntity)->create();
            $sellerId = $orderItem->listing?->seller_id ?? $orderItem->listing()->value('seller_id');

            return [
                'order_item_id' => $orderItem->id,
                'seller_id'     => $sellerId,
                'amount'        => $orderItem->subtotal,
            ];
        });
    }

    public function forOrderItem(?OrderItem $orderItem = null): static
    {
        return $this->state(function (array $attributes) use ($orderItem) {
            $orderItemEntity = $orderItem ?? OrderItem::factory()->create();
            $sellerId = $orderItemEntity->listing?->seller_id ?? $orderItemEntity->listing()->value('seller_id');

            return [
                'order_item_id' => $orderItemEntity->id,
                'seller_id'     => $sellerId,
                'amount'        => $orderItemEntity->subtotal,
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
            'status'       => EscrowStatus::Released,
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
