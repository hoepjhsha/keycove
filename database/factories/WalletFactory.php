<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WalletType;
use App\Models\Seller;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => Seller::factory(),
            'type'      => WalletType::Seller,
            'balance'   => fake()->randomFloat(2, 0, 10000),
            'holding'   => fake()->randomFloat(2, 0, 500),
        ];
    }

    public function forSeller(?Seller $seller = null): static
    {
        return $this->state(function (array $attributes) use ($seller) {
            return [
                'seller_id' => $seller?->id ?? Seller::factory()->create()->id,
            ];
        });
    }

    public function withBalance(float $balance, float $holding = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $balance,
            'holding' => $holding,
        ]);
    }

    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => 0,
            'holding' => 0,
        ]);
    }

    public function rich(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => fake()->randomFloat(2, 50000, 100000),
            'holding' => fake()->randomFloat(2, 1000, 5000),
        ]);
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'seller_id' => null,
            'type'      => WalletType::Internal,
        ]);
    }
}
