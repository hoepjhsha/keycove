<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\OrderItem;
use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reasons = [
            'Product key is invalid or already used',
            'Key does not match the product description',
            'Seller is not responding to messages',
            'Product delivered is different from what was ordered',
            'Key activation failed multiple times',
            'Received wrong region key',
            'Product missing promised DLC or content',
            'Seller provided incorrect installation instructions',
            'Key has been revoked after purchase',
            'Payment processed but no key delivered',
        ];

        return [
            'order_item_id' => OrderItem::factory(),
            'reason' => fake()->randomElement($reasons),
            'evidence' => fake()->optional(0.7)->passthrough([
                fake()->imageUrl(800, 600, 'evidence'),
                'Screenshot showing error message during activation',
                'Email correspondence with seller showing no response',
            ]),
            'status' => ComplaintStatus::Open,
        ];
    }

    public function forOrderItem(?OrderItem $orderItem = null): static
    {
        return $this->state(function (array $attributes) use ($orderItem) {
            return [
                'order_item_id' => $orderItem?->id ?? OrderItem::factory()->create()->id,
            ];
        });
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::Open,
        ]);
    }

    public function inProcess(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::InProcess,
        ]);
    }

    public function escalated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::Escalated,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::Resolved,
        ]);
    }

    public function withEvidence(): static
    {
        return $this->state(fn (array $attributes) => [
            'evidence' => [
                fake()->imageUrl(800, 600, 'evidence'),
                fake()->imageUrl(800, 600, 'evidence'),
                'Detailed description of the issue with timestamps',
            ],
        ]);
    }
}
