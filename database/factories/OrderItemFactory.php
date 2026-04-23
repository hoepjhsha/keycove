<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 9.99, 59.99);
        $subtotal = $quantity * $unitPrice;

        return [
            'order_id'              => Order::factory(),
            'listing_id'            => ProductListing::factory(),
            'product_name_snapshot' => fake()->words(3, true).' - '.fake()->randomElement(['Standard Edition', 'Deluxe Edition', 'Ultimate Edition']),
            'quantity'              => $quantity,
            'unit_price'            => $unitPrice,
            'subtotal'              => $subtotal,
            'status'                => OrderStatus::Processing,
        ];
    }

    public function forOrder(?Order $order = null): static
    {
        return $this->state(function (array $attributes) use ($order) {
            return [
                'order_id' => $order?->id ?? Order::factory()->create()->id,
            ];
        });
    }

    public function withListing(?ProductListing $listing = null): static
    {
        return $this->state(function (array $attributes) use ($listing) {
            $productListing = $listing ?? ProductListing::factory()->create();

            return [
                'listing_id' => $productListing->id,
                'unit_price' => $productListing->price,
                'subtotal'   => $attributes['quantity'] * $productListing->price,
            ];
        });
    }

    public function single(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity' => 1,
                'subtotal' => $attributes['unit_price'],
            ];
        });
    }

    public function bulk(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = fake()->numberBetween(10, 50);

            return [
                'quantity' => $quantity,
                'subtotal' => $quantity * $attributes['unit_price'],
            ];
        });
    }
}
