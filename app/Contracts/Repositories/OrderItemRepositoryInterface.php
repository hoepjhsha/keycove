<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Abstracts\RepositoryInterface;
use App\Models\OrderItem;

interface OrderItemRepositoryInterface extends RepositoryInterface
{
    public function findOwnedByBuyerOrFail(int $buyerId, int $orderItemId): OrderItem;

    public function findCompletedOwnedByBuyerForUpdateOrFail(int $buyerId, int $orderItemId): OrderItem;

    /**
     * @return list<string>
     */
    public function getKeyCodes(OrderItem $orderItem): array;
}
