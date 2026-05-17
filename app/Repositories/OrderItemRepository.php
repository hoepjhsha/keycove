<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Enums\OrderStatus;
use App\Models\OrderItem;

class OrderItemRepository extends Repository implements OrderItemRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new OrderItem);
    }

    /**
     * {@inheritDoc}
     */
    public function findOwnedByBuyerOrFail(int $buyerId, int $orderItemId): OrderItem
    {
        /** @var OrderItem $orderItem */
        $orderItem = $this->newQuery()
            ->whereKey($orderItemId)
            ->whereHas('order', function ($query) use ($buyerId): void {
                $query->where('buyer_id', $buyerId);
            })
            ->with(['complaint', 'review'])
            ->firstOrFail();

        return $orderItem;
    }

    /**
     * {@inheritDoc}
     */
    public function findCompletedOwnedByBuyerForUpdateOrFail(int $buyerId, int $orderItemId): OrderItem
    {
        /** @var OrderItem $orderItem */
        $orderItem = $this->newQuery()
            ->whereKey($orderItemId)
            ->whereHas('order', function ($query) use ($buyerId): void {
                $query->where('buyer_id', $buyerId);
            })
            ->where('status', OrderStatus::Completed->value)
            ->lockForUpdate()
            ->firstOrFail();

        return $orderItem;
    }

    /**
     * {@inheritDoc}
     */
    public function getKeyCodes(OrderItem $orderItem): array
    {
        return $orderItem->keys()
            ->orderBy('id')
            ->get(['id', 'order_item_id', 'key_code'])
            ->pluck('key_code')
            ->filter(fn (?string $keyCode): bool => filled($keyCode))
            ->values()
            ->all();
    }
}
