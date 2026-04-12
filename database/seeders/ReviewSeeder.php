<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\ReviewResponse;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedReviews();
    }

    protected function seedReviews(): void
    {
        // Get order items from completed orders
        $completedOrderItems = OrderItem::whereIn('order_id', function ($query) {
            $query->select('id')
                ->from('orders')
                ->whereIn('status', [OrderStatus::Completed]);
        })->get();

        $sellers = User::where('role', UserRole::Seller)->get();

        if ($completedOrderItems->isEmpty()) {
            return;
        }

        // Rating distribution: 50% 5-star, 24% 4-star, 12% 3-star, 8% 2-star, 6% 1-star
        $ratingDistribution = [
            5 => 25,
            4 => 12,
            3 => 6,
            2 => 4,
            1 => 3,
        ];

        $comments = [
            1 => [
                'Terrible experience. The key didn\'t work at all. Very disappointed.',
                'Scam! The key was already used. Seller not responding.',
                'Worst purchase ever. Do not buy from this seller!',
                'Key invalid, requested refund multiple times with no response.',
                'Complete waste of money. Product key was for wrong region.',
            ],
            2 => [
                'Not what I expected. The key took hours to arrive.',
                'Product is okay but had issues activating. Seller eventually helped.',
                'Mediocre experience. Key works but took too long to deliver.',
                'The key works but customer service was poor.',
                'Had some trouble but it worked in the end. Not great.',
            ],
            3 => [
                'Average product. Nothing special but it works.',
                'Key works fine, delivery was a bit slow.',
                'It\'s okay for the price. No major complaints.',
                'Decent purchase, no complaints but nothing outstanding.',
                'Standard transaction. Key arrived and works as expected.',
            ],
            4 => [
                'Good product! Key worked instantly after activation.',
                'Very satisfied with this purchase. Fast delivery.',
                'Great value for money. Recommended seller!',
                'Smooth transaction, key activated without issues.',
                'Happy with my purchase. Would buy again.',
                'Quick delivery and key works perfectly. Minor delay in email.',
            ],
            5 => [
                'Excellent! Best seller on this platform!',
                'Perfect transaction. Instant delivery and key works flawlessly!',
                'Amazing service! Will definitely buy again.',
                'Outstanding! Quick delivery, great communication, key works perfectly!',
                'Super fast delivery! Key activated immediately. Highly recommended!',
                'Top-notch service! This is how digital purchases should be.',
                'Incredible seller! Key delivered within minutes. 10/10!',
            ],
        ];

        $shuffledItems = $completedOrderItems->shuffle();
        $totalReviews = 0;

        foreach ($ratingDistribution as $rating => $count) {
            for ($i = 0; $i < $count; $i++) {
                $orderItem = $shuffledItems->shift();

                if (! $orderItem) {
                    break;
                }

                // Check if this order item already has a review
                if (Review::where('order_item_id', $orderItem->id)->exists()) {
                    continue;
                }

                $order = $orderItem->order;
                if (! $order) {
                    continue;
                }

                $review = Review::create([
                    'user_id' => $order->buyer_id,
                    'order_item_id' => $orderItem->id,
                    'rating' => $rating,
                    'comment' => fake()->randomElement($comments[$rating]),
                    'media' => fake()->optional(0.3)->passthrough([
                        fake()->imageUrl(800, 600, 'screenshot'),
                    ]),
                    'created_at' => $order->created_at->copy()->addDays(rand(1, 7)),
                    'updated_at' => $order->created_at->copy()->addDays(rand(1, 7)),
                ]);

                $totalReviews++;

                // Some reviews get seller responses (30%)
                if (fake()->boolean(30) && $sellers->isNotEmpty()) {
                    $seller = $sellers->random();

                    $responseComments = [
                        'Thank you for your feedback! We\'re glad you\'re happy with your purchase.',
                        'We appreciate your review! If you need any assistance, feel free to contact us.',
                        'Thanks for choosing our store! We strive to provide the best service.',
                        'Thank you for your support! We hope to serve you again in the future.',
                        'We\'re sorry to hear about your experience. Please contact us so we can resolve this.',
                        'Thank you for your honest review. We\'re working to improve our service.',
                        'We apologize for any inconvenience. Our team is here to help if needed.',
                    ];

                    ReviewResponse::create([
                        'review_id' => $review->id,
                        'replier_id' => $seller->id,
                        'content' => fake()->randomElement($responseComments),
                        'created_at' => $review->created_at->copy()->addHours(rand(1, 48)),
                    ]);
                }
            }
        }
    }
}
