<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\ComplaintMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintMessage>
 */
class ComplaintMessageFactory extends Factory
{
    protected $model = ComplaintMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $messages = [
            'I tried activating the key multiple times but it says it\'s already been used.',
            'Can you please provide a replacement key? This one doesn\'t work.',
            'I\'ve been waiting for 3 days and still no response from the seller.',
            'The key is for the wrong region. I need a US key, not EU.',
            'I followed all the instructions but the activation keeps failing.',
            'Could you please check the order details? Something seems wrong.',
            'The product description mentioned DLC but the key doesn\'t include it.',
            'I need a refund as this key is completely invalid.',
        ];

        return [
            'complaint_id' => Complaint::factory(),
            'sender_id' => User::factory(),
            'message' => fake()->randomElement($messages),
            'attachments' => fake()->optional(0.4)->passthrough([
                fake()->imageUrl(800, 600, 'screenshot'),
            ]),
        ];
    }

    public function forComplaint(?Complaint $complaint = null): static
    {
        return $this->state(function (array $attributes) use ($complaint) {
            return [
                'complaint_id' => $complaint?->id ?? Complaint::factory()->create()->id,
            ];
        });
    }

    public function fromUser(?User $user = null): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'sender_id' => $user?->id ?? User::factory()->create()->id,
            ];
        });
    }

    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => [
                fake()->imageUrl(800, 600, 'screenshot'),
                fake()->imageUrl(800, 600, 'screenshot'),
            ],
        ]);
    }
}
