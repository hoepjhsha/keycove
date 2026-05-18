<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Complaint;
use App\Models\ComplaintMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Utilities\StorageUtility;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;

class MyLibraryService
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private OrderItemRepositoryInterface $orderItems,
    ) {}

    /**
     * @param  array<int, array{keys: list<string>, visible: bool}>  $revealedKeys
     * @return array<string, mixed>
     */
    public function pageData(
        User $user,
        array $revealedKeys,
        ?int $viewingOrderItemId = null,
        ?int $viewingComplaintOrderItemId = null,
        ?int $confirmReceivedOrderItemId = null,
    ): array {
        $user->loadMissing('seller');

        $orders = $this->orders->getLibraryOrdersForBuyer($user->id);
        $selectedOrderItem = $this->selectedOrderItem($orders, $viewingOrderItemId);
        $selectedComplaintOrderItem = $this->selectedOrderItem($orders, $viewingComplaintOrderItemId);
        $selectedComplaintOrder = $selectedComplaintOrderItem !== null
            ? $orders->firstWhere('id', $selectedComplaintOrderItem->order_id)
            : null;
        $selectedComplaint = $selectedComplaintOrderItem?->complaint;
        $selectedReviewMedia = $selectedOrderItem?->review !== null
            ? $this->resolveStoredPaths($selectedOrderItem->review->media)
            : [];
        $selectedConfirmReceivedOrderItem = $this->selectedOrderItem($orders, $confirmReceivedOrderItemId);

        return [
            'user'                             => $user,
            'orders'                           => $orders,
            'pendingPaymentCount'              => $orders->where('payment_status', PaymentStatus::Pending)->count(),
            'completedOrderCount'              => $orders->filter(fn (Order $order): bool => $order->status === OrderStatus::Completed)->count(),
            'hasApprovedSellerAccount'         => $user->seller?->kyc_status === KycStatus::Approved,
            'revealedKeys'                     => $revealedKeys,
            'selectedOrderItem'                => $selectedOrderItem,
            'selectedComplaintOrderItem'       => $selectedComplaintOrderItem,
            'selectedComplaintOrder'           => $selectedComplaintOrder,
            'selectedComplaint'                => $selectedComplaint,
            'selectedComplaintEvidence'        => $this->resolveStoredPaths($selectedComplaint?->evidence),
            'selectedComplaintMessages'        => $this->resolveComplaintMessages($selectedComplaint),
            'selectedReviewMedia'              => $selectedReviewMedia,
            'selectedConfirmReceivedOrderItem' => $selectedConfirmReceivedOrderItem,
        ];
    }

    public function resolveOwnedOrder(User $user, int $orderId): Order
    {
        return $this->orders->findOwnedForLibraryOrFail($user->id, $orderId);
    }

    public function resolveOwnedOrderItem(User $user, int $orderItemId): OrderItem
    {
        return $this->orderItems->findOwnedByBuyerOrFail($user->id, $orderItemId);
    }

    public function canRevealKeys(OrderItem $orderItem): bool
    {
        return in_array($orderItem->status, [
            OrderStatus::Delivered,
            OrderStatus::Disputing,
            OrderStatus::Completed,
        ], true);
    }

    /**
     * @return list<string>
     */
    public function orderItemKeys(OrderItem $orderItem): array
    {
        return $this->orderItems->getKeyCodes($orderItem);
    }

    /**
     * @return array{keys: list<string>, visible: bool}|null
     */
    public function revealedKeyState(OrderItem $orderItem, bool $visible = true): ?array
    {
        if (! $this->canRevealKeys($orderItem)) {
            return null;
        }

        $keys = $this->orderItemKeys($orderItem);

        if ($keys === []) {
            return null;
        }

        return [
            'keys'    => $keys,
            'visible' => $visible,
        ];
    }

    public function markKeysViewed(OrderItem $orderItem): void
    {
        if ($orderItem->buyer_key_viewed_at !== null) {
            return;
        }

        $orderItem->forceFill([
            'buyer_key_viewed_at' => now(),
        ])->save();
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    public function resolveStoredPaths(?array $paths): array
    {
        return collect($paths ?? [])
            ->filter(fn ($path): bool => is_string($path) && $path !== '')
            ->map(fn (string $path): array => [
                'label' => Str::afterLast($path, '/'),
                'url'   => StorageUtility::getUrl($path),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, sender_name: string, message: string, created_at: string|null, attachments: array<int, array{label: string, url: ?string}>}>
     */
    public function resolveComplaintMessages(?Complaint $complaint): array
    {
        if ($complaint === null) {
            return [];
        }

        return $complaint->messages
            ->map(function (ComplaintMessage $message): array {
                return [
                    'id'          => $message->id,
                    'sender_name' => $message->sender?->username ?? 'Hỗ trợ',
                    'message'     => $message->message,
                    'created_at'  => $message->created_at?->format('d/m/Y H:i'),
                    'attachments' => $this->resolveStoredPaths($message->attachments),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  EloquentCollection<int, Order>  $orders
     */
    private function selectedOrderItem(EloquentCollection $orders, ?int $orderItemId): ?OrderItem
    {
        if ($orderItemId === null) {
            return null;
        }

        return $orders
            ->flatMap(fn (Order $order) => $order->items)
            ->firstWhere('id', $orderItemId);
    }
}
