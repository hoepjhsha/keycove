<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InternalWalletDirection;
use App\Enums\InternalWalletEntryType;
use App\Enums\TransactionStatus;
use App\Models\InternalWalletEntry;
use App\Models\Order;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InternalWalletEntry>
 */
class InternalWalletEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id'       => Wallet::factory()->internal(),
            'order_id'        => Order::factory(),
            'type'            => InternalWalletEntryType::PaymentReceived,
            'direction'       => InternalWalletDirection::Inflow,
            'amount'          => fake()->randomFloat(2, 99, 9999),
            'status'          => TransactionStatus::Completed,
            'affects_balance' => true,
            'idempotency_key' => 'internal-wallet-factory-'.fake()->unique()->uuid(),
            'metadata'        => ['source' => 'factory'],
            'occurred_at'     => fake()->dateTimeBetween('-30 days'),
        ];
    }

    public function inflow(): static
    {
        return $this->state(fn (array $attributes): array => [
            'direction'       => InternalWalletDirection::Inflow,
            'affects_balance' => true,
        ]);
    }

    public function outflow(): static
    {
        return $this->state(fn (array $attributes): array => [
            'direction'       => InternalWalletDirection::Outflow,
            'affects_balance' => true,
        ]);
    }

    public function neutral(): static
    {
        return $this->state(fn (array $attributes): array => [
            'direction'       => InternalWalletDirection::Neutral,
            'affects_balance' => false,
        ]);
    }
}
