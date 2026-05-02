<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductKeyStatus;
use App\Models\ProductKey;
use App\Models\ProductListing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductKey>
 */
class ProductKeyFactory extends Factory
{
    protected $model = ProductKey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $keyCode = $this->generateKeyCode();

        return [
            'listing_id'    => ProductListing::factory(),
            'key_code'      => $keyCode,
            'key_hash'      => hash('sha256', $keyCode),
            'status'        => ProductKeyStatus::Available,
            'order_item_id' => null,
        ];
    }

    /**
     * Generate a realistic product key in format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX
     */
    protected function generateKeyCode(): string
    {
        $segments = [];

        for ($i = 0; $i < 5; $i++) {
            $segments[] = strtoupper(Str::random(5));
        }

        return implode('-', $segments);
    }

    public function withListing(?ProductListing $listing = null): static
    {
        return $this->state(function (array $attributes) use ($listing) {
            return [
                'listing_id' => $listing?->id ?? ProductListing::factory()->create()->id,
            ];
        });
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => ProductKeyStatus::Available,
            'order_item_id' => null,
        ]);
    }

    public function reserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductKeyStatus::Reserved,
        ]);
    }

    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductKeyStatus::Sold,
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductKeyStatus::Refunded,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductKeyStatus::Disabled,
        ]);
    }
}
