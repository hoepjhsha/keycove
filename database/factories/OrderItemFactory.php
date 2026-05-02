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
        $listing = ProductListing::factory()->create();
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = (float) $listing->price;
        $subtotal = $quantity * $unitPrice;
        $platformFee = $listing->seller_id === null ? 0 : round($subtotal * 0.1, 2);

        return [
            'order_id'              => Order::factory(),
            'listing_id'            => $listing->id,
            'seller_id'             => $listing->seller_id,
            'product_name_snapshot' => fake()->words(3, true).' - '.fake()->randomElement(['Standard Edition', 'Deluxe Edition', 'Ultimate Edition']),
            'variant_snapshot'      => [
                'variant_id' => $listing->variant_id,
            ],
            'quantity'      => $quantity,
            'unit_price'    => $unitPrice,
            'subtotal'      => $subtotal,
            'platform_fee'  => $platformFee,
            'seller_amount' => $listing->seller_id === null ? 0 : round($subtotal - $platformFee, 2),
            'status'        => OrderStatus::Processing,
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
                'listing_id'    => $productListing->id,
                'seller_id'     => $productListing->seller_id,
                'unit_price'    => $productListing->price,
                'subtotal'      => $attributes['quantity'] * $productListing->price,
                'platform_fee'  => $productListing->seller_id === null ? 0 : round(($attributes['quantity'] * (float) $productListing->price) * 0.1, 2),
                'seller_amount' => $productListing->seller_id === null ? 0 : round(($attributes['quantity'] * (float) $productListing->price) * 0.9, 2),
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
