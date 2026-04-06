<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rating = fake()->numberBetween(1, 5);
        $comments = [
            1 => [
                'Terrible product, complete waste of money.',
                'Key didn\'t work at all. Very disappointed.',
                'Scam! The key was already used.',
                'Worst purchase ever. Do not buy!',
            ],
            2 => [
                'Not what I expected. The key took hours to arrive.',
                'Product is okay but had issues activating.',
                'Mediocre experience. Could be better.',
                'The key works but customer service was poor.',
            ],
            3 => [
                'Average product. Nothing special.',
                'Key works fine, delivery was slow.',
                'It\'s okay for the price.',
                'Decent purchase, no complaints but nothing outstanding.',
            ],
            4 => [
                'Good product! Key worked instantly.',
                'Very satisfied with this purchase. Fast delivery.',
                'Great value for money. Recommended!',
                'Smooth transaction, key activated without issues.',
            ],
            5 => [
                'Excellent! Best seller on this platform!',
                'Perfect transaction. Instant delivery and key works flawlessly!',
                'Amazing service! Will definitely buy again.',
                'Outstanding! Quick delivery, great communication, key works perfectly!',
            ],
        ];

        return [
            'user_id' => User::factory(),
            'order_item_id' => OrderItem::factory(),
            'rating' => $rating,
            'comment' => fake()->randomElement($comments[$rating]),
            'media' => fake()->optional(0.3)->passthrough([
                fake()->imageUrl(800, 600, 'screenshot'),
                fake()->imageUrl(800, 600, 'screenshot'),
            ]),
        ];
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user?->id ?? User::factory()->create()->id,
            ];
        });
    }

    public function forOrderItem(?OrderItem $orderItem = null): static
    {
        return $this->state(function (array $attributes) use ($orderItem) {
            return [
                'order_item_id' => $orderItem?->id ?? OrderItem::factory()->create()->id,
            ];
        });
    }

    public function oneStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 1,
            'comment' => fake()->randomElement([
                'Terrible product, complete waste of money.',
                'Key didn\'t work at all. Very disappointed.',
                'Scam! The key was already used.',
            ]),
        ]);
    }

    public function fiveStars(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 5,
            'comment' => fake()->randomElement([
                'Excellent! Best seller on this platform!',
                'Perfect transaction. Instant delivery and key works flawlessly!',
                'Amazing service! Will definitely buy again.',
            ]),
        ]);
    }

    public function withMedia(): static
    {
        return $this->state(fn (array $attributes) => [
            'media' => [
                fake()->imageUrl(800, 600, 'screenshot'),
                fake()->imageUrl(800, 600, 'screenshot'),
            ],
        ]);
    }
}
