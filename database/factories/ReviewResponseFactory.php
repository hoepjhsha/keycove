<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Review;
use App\Models\ReviewResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewResponse>
 */
class ReviewResponseFactory extends Factory
{
    protected $model = ReviewResponse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $responses = [
            'Thank you for your feedback! We appreciate your business.',
            'We\'re glad you enjoyed your purchase! Feel free to contact us anytime.',
            'Thank you for the 5-star review! We look forward to serving you again.',
            'We apologize for any inconvenience. Please contact our support team for assistance.',
            'We take your concerns seriously. Our team will reach out to help resolve this issue.',
            'Thank you for bringing this to our attention. We\'re working to improve our service.',
            'We appreciate your honest feedback and will use it to improve.',
            'Glad we could help! Don\'t hesitate to reach out if you need anything else.',
        ];

        return [
            'review_id' => Review::factory(),
            'replier_id' => User::factory()->seller(),
            'content' => fake()->randomElement($responses),
        ];
    }

    public function forReview(?Review $review = null): static
    {
        return $this->state(function (array $attributes) use ($review) {
            return [
                'review_id' => $review?->id ?? Review::factory()->create()->id,
            ];
        });
    }

    public function fromSeller(?User $seller = null): static
    {
        return $this->state(function (array $attributes) use ($seller) {
            return [
                'replier_id' => $seller?->id ?? User::factory()->seller()->create()->id,
            ];
        });
    }
}
