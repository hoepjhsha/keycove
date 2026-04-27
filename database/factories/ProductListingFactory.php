<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductListingStatus;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductListing>
 */
class ProductListingFactory extends Factory
{
    protected $model = ProductListing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $displayName = fake()->sentence(3);

        return [
            'variant_id'   => ProductVariant::factory(),
            'seller_id'    => Seller::factory(),
            'display_name' => $displayName,
            'slug'         => Str::slug($displayName).'-'.fake()->unique()->numberBetween(1000, 9999),
            'price'        => fake()->randomFloat(2, 9.99, 59.99),
            'stock_count'  => fake()->numberBetween(0, 100),
            'status'       => ProductListingStatus::Active,
        ];
    }

    public function withVariant(?ProductVariant $variant = null): static
    {
        return $this->state(function (array $attributes) use ($variant) {
            return [
                'variant_id' => $variant?->id ?? ProductVariant::factory()->create()->id,
            ];
        });
    }

    public function withSeller(?Seller $seller = null): static
    {
        return $this->state(function (array $attributes) use ($seller) {
            return [
                'seller_id' => $seller?->id ?? Seller::factory()->create()->id,
            ];
        });
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductListingStatus::Draft,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductListingStatus::Pending,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductListingStatus::Active,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductListingStatus::Hidden,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductListingStatus::Rejected,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductListingStatus::Closed,
        ]);
    }

    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->randomFloat(2, 4.99, 19.99),
        ]);
    }

    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->randomFloat(2, 49.99, 79.99),
        ]);
    }
}
