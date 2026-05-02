<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductVariantStatus;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $editions = [
            'Standard Edition',
            'Deluxe Edition',
            'Ultimate Edition',
            'Gold Edition',
            'Premium Edition',
            'Collector\'s Edition',
            'Game of the Year Edition',
            'Complete Edition',
            'Definitive Edition',
            'Remastered Edition',
            'Enhanced Edition',
            'Special Edition',
            'Limited Edition',
            'Digital Deluxe Edition',
        ];

        return [
            'product_id'  => Product::factory(),
            'region_id'   => Region::factory(),
            'platform_id' => Platform::factory(),
            'os_id'       => OperatingSystem::factory(),
            'edition'     => fake()->randomElement($editions),
            'status'      => ProductVariantStatus::Active,
        ];
    }

    public function withProduct(?Product $product = null): static
    {
        return $this->state(function (array $attributes) use ($product) {
            return [
                'product_id' => $product?->id ?? Product::factory()->create()->id,
            ];
        });
    }

    public function withRegion(?Region $region = null): static
    {
        return $this->state(function (array $attributes) use ($region) {
            return [
                'region_id' => $region?->id ?? Region::factory()->create()->id,
            ];
        });
    }

    public function withPlatform(?Platform $platform = null): static
    {
        return $this->state(function (array $attributes) use ($platform) {
            return [
                'platform_id' => $platform?->id ?? Platform::factory()->create()->id,
            ];
        });
    }

    public function withOS(?OperatingSystem $os = null): static
    {
        return $this->state(function (array $attributes) use ($os) {
            return [
                'os_id' => $os?->id ?? OperatingSystem::factory()->create()->id,
            ];
        });
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductVariantStatus::Draft,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductVariantStatus::Active,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductVariantStatus::Hidden,
        ]);
    }

    public function discontinued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductVariantStatus::Discontinued,
        ]);
    }
}
