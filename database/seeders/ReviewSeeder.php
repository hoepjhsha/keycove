<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\ReviewResponse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviewableItems = OrderItem::query()
            ->with(['order.buyer', 'seller.user'])
            ->whereIn('status', [OrderStatus::Delivered, OrderStatus::Completed])
            ->doesntHave('review')
            ->get()
            ->shuffle()
            ->take((int) floor(OrderItem::query()->whereIn('status', [OrderStatus::Delivered, OrderStatus::Completed])->count() * 0.32));

        foreach ($reviewableItems as $orderItem) {
            $rating = fake()->randomElement([5, 5, 5, 4, 4, 4, 4, 3, 2, 1]);
            $createdAt = Carbon::parse($orderItem->updated_at)->copy()->addDays(random_int(1, 14))->addHours(random_int(1, 12));

            $review = Review::create([
                'user_id'       => $orderItem->order->buyer_id,
                'order_item_id' => $orderItem->id,
                'rating'        => $rating,
                'comment'       => $this->commentForRating($rating, $orderItem->product_name_snapshot),
                'media'         => fake()->boolean(12)
                    ? ['https://placehold.co/1280x720/png?text='.urlencode(Str::limit($orderItem->product_name_snapshot, 36, ''))]
                    : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            if ($orderItem->seller?->user_id === null) {
                continue;
            }

            if ($rating <= 3 || fake()->boolean(35)) {
                $responseCreatedAt = $createdAt->copy()->addHours(random_int(4, 72));

                ReviewResponse::create([
                    'review_id'  => $review->id,
                    'replier_id' => $orderItem->seller->user_id,
                    'content'    => $this->responseForRating($rating),
                    'created_at' => $responseCreatedAt,
                ]);
            }
        }
    }

    protected function commentForRating(int $rating, string $productName): string
    {
        $comments = match ($rating) {
            5 => [
                'Key giao nhanh, kich hoat '.$productName.' on ngay, se quay lai mua tiep.',
                'Gia hop ly, seller tra loi nhanh, don '.$productName.' rat muot.',
                'San pham dung mo ta, nhan key gan nhu ngay lap tuc.',
            ],
            4 => [
                'Don '.$productName.' on, kich hoat duoc, chi hoi cham mot chut.',
                'Mua lan dau thay kha hai long, key dung va ho tro on.',
                'Gia tot, giao key nhanh, mo ta san pham kha chuan.',
            ],
            3 => [
                'Key van dung nhung thoi gian cho hoi lau hon du kien.',
                'San pham '.$productName.' dung duoc, nhung can mo ta ro hon ve region.',
                'Tam on, khong co van de lon nhung trai nghiem chua that su mem.',
            ],
            2 => [
                'Nhan key hoi cham va phai nhan ho tro moi kich hoat duoc.',
                'Seller co phan cham phan hoi, trai nghiem voi '.$productName.' chua tot.',
                'Key dung nhung xu ly phat sinh kha met.',
            ],
            default => [
                'Gap loi kich hoat, phai lien he nhieu lan moi xu ly duoc.',
                'Trai nghiem khong tot, key/phien ban nhan duoc khong nhu ky vong.',
                'Ho tro cham, phat sinh nhieu van de voi don nay.',
            ],
        };

        return fake()->randomElement($comments);
    }

    protected function responseForRating(int $rating): string
    {
        if ($rating >= 4) {
            return fake()->randomElement([
                'Cam on ban da ung ho shop. Neu can them key hoac gia han, ben minh ho tro nhanh.',
                'Cam on phan hoi tich cuc cua ban. Shop se co gang giu toc do giao key on dinh hon nua.',
            ]);
        }

        return fake()->randomElement([
            'Shop xin loi ve trai nghiem chua tot. Neu ban can doi key hoac ho tro them, shop se xu ly tiep.',
            'Cam on ban da de lai phan hoi. Ben minh da ghi nhan van de va se uu tien ho tro nhanh hon.',
        ]);
    }
}
