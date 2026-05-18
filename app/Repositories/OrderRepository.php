<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class OrderRepository extends Repository implements OrderRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Order);
    }

    /**
     * {@inheritDoc}
     */
    public function getLibraryOrdersForBuyer(int $buyerId, int $limit = 12): EloquentCollection
    {
        return $this->newQuery()
            ->where('buyer_id', $buyerId)
            ->with([
                'items' => fn ($query) => $query
                    ->select([
                        'id',
                        'order_id',
                        'listing_id',
                        'order_item_code',
                        'product_name_snapshot',
                        'quantity',
                        'unit_price',
                        'subtotal',
                        'status',
                        'buyer_key_viewed_at',
                    ])
                    ->with([
                        'listing.variant.product',
                        'listing.variant.region',
                        'listing.variant.platform',
                        'listing.variant.operatingSystem',
                        'complaint.messages.sender',
                        'complaint.resolvedBy',
                        'review',
                    ])
                    ->withCount('keys')
                    ->orderBy('id'),
            ])
            ->withCount('items')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findOwnedForLibraryOrFail(int $buyerId, int $orderId): Order
    {
        /** @var Order $order */
        $order = $this->newQuery()
            ->where('buyer_id', $buyerId)
            ->with(['items.keys', 'items.escrow', 'paymentTransactions', 'transactions'])
            ->findOrFail($orderId);

        return $order;
    }
}
