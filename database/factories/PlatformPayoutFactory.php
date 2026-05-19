<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlatformPayoutStatus;
use App\Models\PlatformPayout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformPayout>
 */
class PlatformPayoutFactory extends Factory
{
    protected $model = PlatformPayout::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $periodEnd = fake()->dateTimeBetween('-10 days', '-3 days');
        $periodStart = (clone $periodEnd)->modify('-6 days');

        return [
            'payout_code'          => 'PPO-'.fake()->unique()->numerify('######'),
            'period_start'         => $periodStart,
            'period_end'           => $periodEnd,
            'settlement_cutoff_at' => $periodEnd,
            'amount'               => fake()->randomFloat(2, 100, 5000),
            'status'               => PlatformPayoutStatus::Pending,
            'bank_name'            => fake()->randomElement(['Vietcombank', 'Techcombank', 'BIDV']),
            'bank_code'            => fake()->randomElement(['VCB', 'TCB', 'BIDV']),
            'bank_account_number'  => fake()->numerify('##########'),
            'bank_account_name'    => fake()->name(),
            'processed_at'         => null,
            'idempotency_key'      => 'platform-payout-'.fake()->unique()->uuid(),
            'metadata'             => ['source' => 'factory'],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status'       => PlatformPayoutStatus::Completed,
            'processed_at' => now(),
        ]);
    }
}
