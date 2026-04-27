<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\ReviewResponse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

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
        $completedOrderItems = OrderItem::with(['order', 'seller.user'])
            ->where('status', OrderStatus::Completed)
            ->whereHas('order')
            ->get()
            ->shuffle();

        if ($completedOrderItems->isEmpty()) {
            return;
        }

        $ratingDistribution = [
            5 => 8,
            4 => 5,
            3 => 3,
            2 => 2,
            1 => 1,
        ];

        $comments = [
            1 => [
                'Key không hoạt động và shop phản hồi khá chậm.',
                'Mã đã bị dùng trước đó, phải mở tranh chấp.',
                'Trải nghiệm tệ, sản phẩm không đúng mô tả.',
            ],
            2 => [
                'Key dùng được nhưng giao hơi chậm.',
                'Bán đúng hàng nhưng support chưa tốt.',
                'Ổn nhưng không đúng kỳ vọng ban đầu.',
            ],
            3 => [
                'Giao dịch ở mức ổn, key kích hoạt được.',
                'Sản phẩm đúng mô tả, cần cải thiện tốc độ giao.',
                'Mua xong dùng bình thường, không có gì nổi bật.',
            ],
            4 => [
                'Key hoạt động tốt, giao hàng khá nhanh.',
                'Mọi thứ ổn, shop phản hồi rõ ràng.',
                'Đúng mô tả, đáng tiền.',
            ],
            5 => [
                'Key giao rất nhanh, kích hoạt ngay.',
                'Shop uy tín, hàng chuẩn, sẽ mua lại.',
                'Trải nghiệm tốt, giao dịch rất mượt.',
                'Đúng key cần mua, support nhanh.',
            ],
        ];

        foreach ($ratingDistribution as $rating => $count) {
            for ($i = 0; $i < $count; $i++) {
                $orderItem = $completedOrderItems->shift();

                if (! $orderItem) {
                    return;
                }

                if (Review::where('order_item_id', $orderItem->id)->exists()) {
                    continue;
                }

                $order = $orderItem->order;
                if (! $order) {
                    continue;
                }

                $reviewDate = Carbon::parse($order->created_at)->addDays(random_int(1, 10));

                $review = Review::create([
                    'user_id'       => $order->buyer_id,
                    'order_item_id' => $orderItem->id,
                    'rating'        => $rating,
                    'comment'       => fake()->randomElement($comments[$rating]),
                    'media'         => fake()->optional(0.2)->passthrough([
                        fake()->imageUrl(1200, 900, 'screenshot'),
                    ]),
                ]);

                $review->forceFill([
                    'created_at' => $reviewDate,
                    'updated_at' => $reviewDate,
                ])->saveQuietly();

                $sellerUser = $orderItem->seller?->user;

                if ($sellerUser && fake()->boolean(35)) {
                    $responseDate = $reviewDate->copy()->addHours(random_int(2, 48));

                    $response = ReviewResponse::create([
                        'review_id'  => $review->id,
                        'replier_id' => $sellerUser->id,
                        'content'    => fake()->randomElement([
                            'Cảm ơn bạn đã đánh giá, shop sẽ tiếp tục cải thiện chất lượng phục vụ.',
                            'Rất vui vì bạn hài lòng, cảm ơn bạn đã ủng hộ shop.',
                            'Cảm ơn phản hồi của bạn, nếu cần hỗ trợ thêm hãy nhắn shop nhé.',
                            'Chúng tôi ghi nhận góp ý và sẽ xử lý tốt hơn ở các đơn sau.',
                        ]),
                    ]);

                    $response->forceFill([
                        'created_at' => $responseDate,
                    ])->saveQuietly();
                }
            }
        }
    }
}
