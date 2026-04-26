<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentTransaction>
 */
class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'               => Order::factory(),
            'gateway'                => fake()->randomElement([PaymentMethod::VNPay, PaymentMethod::Stripe]),
            'gateway_transaction_id' => 'PAY'.fake()->numerify('##############'),
            'amount'                 => fake()->randomFloat(2, 50, 5000),
            'status'                 => PaymentStatus::Pending,
            'request_payload'        => null,
            'response_payload'       => null,
            'paid_at'                => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'  => PaymentStatus::Completed,
            'paid_at' => now(),
        ]);
    }
}
