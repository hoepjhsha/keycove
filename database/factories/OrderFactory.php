<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'buyer_id' => User::factory(),
            'order_code' => $this->generateOrderCode(),
            'total_price' => fake()->randomFloat(2, 9.99, 299.99),
            'status' => OrderStatus::Processing,
            'payment_method' => fake()->randomElement([PaymentMethod::VNPay, PaymentMethod::Stripe]),
        ];
    }

    /**
     * Generate unique order code in format: ORD-YYYYMMDD-XXXXX
     */
    protected function generateOrderCode(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.strtoupper(fake()->bothify('?????'));
    }

    public function forBuyer(?User $buyer = null): static
    {
        return $this->state(function (array $attributes) use ($buyer) {
            return [
                'buyer_id' => $buyer?->id ?? User::factory()->create()->id,
            ];
        });
    }

    public function pendingPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PendingPayment,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Processing,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Delivered,
        ]);
    }

    public function disputing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Disputing,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Completed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Refunded,
        ]);
    }

    public function vnpay(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::VNPay,
        ]);
    }

    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::Stripe,
        ]);
    }
}
