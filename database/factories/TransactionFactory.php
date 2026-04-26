<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([TransactionType::PaymentReceived, TransactionType::Withdraw]);
        $amount = fake()->randomFloat(2, 9.99, 999.99);

        $paymentInfo = $type === TransactionType::PaymentReceived
            ? $this->generatePaymentInfo()
            : $this->generateWithdrawInfo();

        return [
            'wallet_id'    => Wallet::factory(),
            'order_id'     => $type === TransactionType::PaymentReceived ? Order::factory() : null,
            'type'         => $type,
            'balance_type' => TransactionBalanceType::Available,
            'payment_info' => $paymentInfo,
            'amount'       => $type === TransactionType::Withdraw ? -$amount : $amount,
            'status'       => TransactionStatus::Completed,
        ];
    }

    protected function generatePaymentInfo(): array
    {
        $method = fake()->randomElement([PaymentMethod::VNPay, PaymentMethod::Stripe]);

        if ($method === PaymentMethod::VNPay) {
            return [
                'method'         => 'VNPay',
                'transaction_id' => 'VNP'.fake()->numerify('##############'),
                'bank_code'      => fake()->randomElement(['NCB', 'VIETCOMBANK', 'TECHCOMBANK', 'SACOMBANK', 'BIDV']),
                'card_type'      => fake()->randomElement(['ATM', 'VISA', 'MASTERCARD']),
                'response_code'  => '00',
            ];
        }

        return [
            'method'            => 'Stripe',
            'transaction_id'    => 'pi_'.fake()->bothify('????####################'),
            'payment_method_id' => 'pm_'.fake()->bothify('????####################'),
            'card_brand'        => fake()->randomElement(['visa', 'mastercard', 'amex']),
            'last4'             => fake()->numerify('####'),
        ];
    }

    protected function generateWithdrawInfo(): array
    {
        return [
            'bank_name'      => fake()->randomElement(['Vietcombank', 'Techcombank', 'BIDV', 'ACB', 'Sacombank']),
            'account_number' => fake()->numerify('##########'),
            'account_holder' => fake()->name(),
            'note'           => 'Withdrawal request',
        ];
    }

    public function forOrder(?Order $order = null): static
    {
        return $this->state(function (array $attributes) use ($order) {
            $orderEntity = $order ?? Order::factory()->create();

            return [
                'order_id'     => $orderEntity->id,
                'wallet_id'    => Wallet::factory()->internal()->create()->id,
                'type'         => TransactionType::PaymentReceived,
                'balance_type' => TransactionBalanceType::Available,
                'amount'       => $orderEntity->total_price,
                'payment_info' => $this->generatePaymentInfo(),
            ];
        });
    }

    public function forWallet(?Wallet $wallet = null): static
    {
        return $this->state(function (array $attributes) use ($wallet) {
            return [
                'order_id'     => null,
                'wallet_id'    => $wallet?->id ?? Wallet::factory()->create()->id,
                'type'         => TransactionType::Withdraw,
                'balance_type' => TransactionBalanceType::Available,
                'amount'       => -fake()->randomFloat(2, 50, 5000),
                'payment_info' => $this->generateWithdrawInfo(),
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Pending,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Completed,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Failed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Cancelled,
        ]);
    }

    public function vnpay(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'         => TransactionType::PaymentReceived,
            'payment_info' => array_merge($this->generatePaymentInfo(), ['method' => 'VNPay']),
        ]);
    }

    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'         => TransactionType::PaymentReceived,
            'payment_info' => array_merge($this->generatePaymentInfo(), ['method' => 'Stripe']),
        ]);
    }
}
