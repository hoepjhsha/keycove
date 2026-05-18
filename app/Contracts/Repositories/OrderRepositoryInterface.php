<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Abstracts\RepositoryInterface;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface OrderRepositoryInterface extends RepositoryInterface
{
    /**
     * @return EloquentCollection<int, Order>
     */
    public function getLibraryOrdersForBuyer(int $buyerId, int $limit = 12): EloquentCollection;

    public function findOwnedForLibraryOrFail(int $buyerId, int $orderId): Order;
}
