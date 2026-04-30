<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Library;

use App\Enums\ComplaintStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Managers\PaymentManager;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Shop\PendingOrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Library')]
class MyLibrary extends Component
{
    public string $keyAccessPassword = '';

    public ?int $keyAccessOrderItemId = null;

    public string $complaintReason = '';

    public ?int $complaintOrderItemId = null;

    /**
     * @var array<int, list<string>>
     */
    public array $revealedKeys = [];

    protected bool $hasComplaintCodeColumn = false;

    public function mount(): void
    {
        $this->hasComplaintCodeColumn = Schema::hasColumn('complaints', 'complaint_code');
    }

    public function continuePayment(int $orderId, PendingOrderService $pendingOrderService, PaymentManager $paymentManager)
    {
        return redirect()->away($pendingOrderService->paymentUrl($this->resolveOwnedOrder($orderId), $paymentManager));
    }

    public function cancelOrder(int $orderId, PendingOrderService $pendingOrderService): void
    {
        $pendingOrderService->cancel($this->resolveOwnedOrder($orderId));

        session()->flash('library-status', 'The pending order has been cancelled and reserved keys were released.');
    }

    public function promptKeyReveal(int $orderItemId): void
    {
        $orderItem = $this->resolveOwnedOrderItem($orderItemId);

        if (! $this->canRevealKeys($orderItem)) {
            return;
        }

        $keys = $this->orderItemKeys($orderItem);

        if ($keys === []) {
            return;
        }

        if ($orderItem->buyer_key_viewed_at !== null) {
            $this->revealedKeys[$orderItem->id] = $keys;
            $this->keyAccessOrderItemId = null;
            $this->keyAccessPassword = '';

            return;
        }

        $this->resetValidation('keyAccessPassword');
        $this->keyAccessPassword = '';
        $this->keyAccessOrderItemId = $orderItem->id;
    }

    public function cancelKeyReveal(): void
    {
        $this->resetValidation('keyAccessPassword');
        $this->keyAccessPassword = '';
        $this->keyAccessOrderItemId = null;
    }

    public function revealOrderItemKeys(): void
    {
        $this->validate([
            'keyAccessPassword' => ['required', 'current_password'],
        ]);

        $orderItem = $this->resolveOwnedOrderItem((int) $this->keyAccessOrderItemId);

        if (! $this->canRevealKeys($orderItem)) {
            return;
        }

        $keys = $this->orderItemKeys($orderItem);

        if ($keys === []) {
            $this->addError('keyAccessPassword', 'No product keys are attached to this order item yet.');

            return;
        }

        $this->revealedKeys[$orderItem->id] = $keys;

        if ($orderItem->buyer_key_viewed_at === null) {
            $orderItem->forceFill([
                'buyer_key_viewed_at' => now(),
            ])->save();
        }

        $this->keyAccessPassword = '';
        $this->keyAccessOrderItemId = null;
        session()->flash('library-status', 'Your key is now visible below. Stay on this screen while reviewing and activating it.');
    }

    public function hideOrderItemKeys(int $orderItemId): void
    {
        unset($this->revealedKeys[$orderItemId]);
    }

    public function confirmReceived(int $orderItemId): void
    {
        $item = $this->resolveOwnedOrderItem($orderItemId);

        $item->forceFill([
            'status' => OrderStatus::Completed,
        ])->save();

        session()->flash('library-status', 'The order item has been marked as completed.');
    }

    public function openComplaintForm(int $orderItemId): void
    {
        $this->complaintOrderItemId = $this->resolveOwnedOrderItem($orderItemId)->id;
        $this->complaintReason = '';
        $this->resetValidation('complaintReason');
    }

    public function cancelComplaintForm(): void
    {
        $this->complaintOrderItemId = null;
        $this->complaintReason = '';
        $this->resetValidation('complaintReason');
    }

    public function submitComplaint(): void
    {
        $this->validate([
            'complaintReason' => ['required', 'string', 'min:10'],
        ]);

        $item = $this->resolveOwnedOrderItem((int) $this->complaintOrderItemId);

        if ($item->complaint()->exists()) {
            $this->cancelComplaintForm();

            return;
        }

        $attributes = [
            'order_item_id' => $item->id,
            'reason'        => $this->complaintReason,
            'status'        => ComplaintStatus::Open,
        ];

        if ($this->hasComplaintCodeColumn) {
            $attributes['complaint_code'] = 'CMP-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        }

        Complaint::create($attributes);

        $item->forceFill([
            'status' => OrderStatus::Disputing,
        ])->save();

        $this->cancelComplaintForm();
        session()->flash('library-status', 'Your complaint has been opened and the item is now marked as disputing.');
    }

    public function render(): View
    {
        $user = $this->resolveUser();

        $orders = $user->orders()
            ->with([
                'items' => fn ($query) => $query
                    ->select(['id', 'order_id', 'listing_id', 'product_name_snapshot', 'quantity', 'unit_price', 'subtotal', 'status', 'buyer_key_viewed_at'])
                    ->with(['listing.variant.product', 'complaint'])
                    ->withCount('keys')
                    ->orderBy('id'),
            ])
            ->withCount('items')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        return view('pages.shop.library.my-library', [
            'orders'              => $orders,
            'pendingPaymentCount' => $orders->where('payment_status', PaymentStatus::Pending)->count(),
            'completedOrderCount' => $orders->filter(fn (Order $order): bool => $order->status === OrderStatus::Completed)->count(),
            'revealedKeys'        => $this->revealedKeys,
        ])->layout('components.layouts.shop');
    }

    protected function resolveUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    protected function resolveOwnedOrder(int $orderId): Order
    {
        return $this->resolveUser()
            ->orders()
            ->with(['items.keys', 'items.escrow', 'paymentTransactions', 'transactions'])
            ->findOrFail($orderId);
    }

    protected function resolveOwnedOrderItem(int $orderItemId): OrderItem
    {
        return OrderItem::query()
            ->whereKey($orderItemId)
            ->whereHas('order', function ($query): void {
                $query->where('buyer_id', $this->resolveUser()->id);
            })
            ->with(['complaint'])
            ->firstOrFail();
    }

    protected function canRevealKeys(OrderItem $orderItem): bool
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
    protected function orderItemKeys(OrderItem $orderItem): array
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
