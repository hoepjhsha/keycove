<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Wallet;
use App\Models\Withdraw;
use App\Enums\WithdrawStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdraw>
 */
class WithdrawFactory extends Factory
{
    protected $model = Withdraw::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'status' => WithdrawStatus::Pending,
        ];
    }

    public function forWallet(?Wallet $wallet = null): static
    {
        return $this->state(function (array $attributes) use ($wallet) {
            return [
                'wallet_id' => $wallet?->id ?? Wallet::factory()->create()->id,
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WithdrawStatus::Pending,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WithdrawStatus::Processing,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WithdrawStatus::Completed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WithdrawStatus::Cancelled,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WithdrawStatus::Failed,
        ]);
    }

    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => fake()->randomFloat(2, 10, 100),
        ]);
    }

    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => fake()->randomFloat(2, 5000, 50000),
        ]);
    }
}
