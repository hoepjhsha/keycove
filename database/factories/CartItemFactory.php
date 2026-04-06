<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'listing_id' => ProductListing::factory(),
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }

    public function forCart(?Cart $cart = null): static
    {
        return $this->state(function (array $attributes) use ($cart) {
            return [
                'cart_id' => $cart?->id ?? Cart::factory()->create()->id,
            ];
        });
    }

    public function withListing(?ProductListing $listing = null): static
    {
        return $this->state(function (array $attributes) use ($listing) {
            return [
                'listing_id' => $listing?->id ?? ProductListing::factory()->create()->id,
            ];
        });
    }

    public function single(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 1,
        ]);
    }

    public function bulk(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(10, 50),
        ]);
    }
}
