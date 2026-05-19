<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Models\User;
use App\Utilities\StorageUtility;
use Illuminate\Support\Facades\DB;

class OrderReviewService
{
    public function __construct(
        private OrderItemRepositoryInterface $orderItems,
        private ReviewRepositoryInterface $reviews,
    ) {}

    /**
     * @param  array<int, mixed>  $media
     */
    public function submit(User $user, int $orderItemId, int $rating, ?string $comment, array $media): bool
    {
        return DB::transaction(function () use ($user, $orderItemId, $rating, $comment, $media): bool {
            $item = $this->orderItems->findCompletedOwnedByBuyerForUpdateOrFail($user->id, $orderItemId);

            if ($item->review()->exists()) {
                return false;
            }

            $this->reviews->create([
                'user_id'       => $user->id,
                'order_item_id' => $item->id,
                'rating'        => $rating,
                'comment'       => $this->normalizeComment($comment),
                'media'         => $this->storeUploadedFiles($media, 'reviews/media'),
            ]);

            return true;
        }, attempts: 3);
    }

    private function normalizeComment(?string $comment): ?string
    {
        $normalizedComment = trim((string) $comment);

        return $normalizedComment !== '' ? $normalizedComment : null;
    }

    /**
     * @param  array<int, mixed>  $files
     * @return list<string>
     */
    private function storeUploadedFiles(array $files, string $directory): array
    {
        return collect($files)
            ->filter()
            ->map(function ($file) use ($directory): string|false {
                return StorageUtility::store($file, $directory, config('filesystems.public_disk'));
            })
            ->filter(fn ($path): bool => is_string($path) && $path !== '')
            ->values()
            ->all();
    }
}
