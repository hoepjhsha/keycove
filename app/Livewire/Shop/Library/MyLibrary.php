<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Library;

use App\Enums\ComplaintStatus;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Managers\PaymentManager;
use App\Models\Complaint;
use App\Models\ComplaintMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use App\Services\Shop\ComplaintService;
use App\Services\Shop\PendingOrderService;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Thư viện của tôi')]
class MyLibrary extends Component
{
    use WithFileUploads;

    public string $keyAccessPassword = '';

    public ?int $keyAccessOrderItemId = null;

    public ?int $viewingOrderItemId = null;

    public ?int $confirmReceivedOrderItemId = null;

    public ?int $reviewOrderItemId = null;

    public string $reviewRating = '5';

    public string $reviewComment = '';

    /**
     * @var array<int, UploadedFile>
     */
    public array $reviewMedia = [];

    public string $complaintReason = '';

    /**
     * @var array<int, UploadedFile>
     */
    public array $complaintEvidence = [];

    public ?int $complaintOrderItemId = null;

    public ?int $viewingComplaintOrderItemId = null;

    public string $complaintReplyMessage = '';

    /**
     * @var array<int, UploadedFile>
     */
    public array $complaintReplyAttachments = [];

    /**
     * @var array<int, array{keys: list<string>, visible: bool}>
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

        session()->flash('library-status', 'Đơn chờ thanh toán đã bị hủy và các key đã giữ chỗ được giải phóng.');
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
            $this->revealedKeys[$orderItem->id] = [
                'keys'    => $keys,
                'visible' => true,
            ];
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

    public function openItemDetails(int $orderItemId): void
    {
        $this->viewingOrderItemId = $this->resolveOwnedOrderItem($orderItemId)->id;
    }

    public function closeItemDetails(): void
    {
        $this->viewingOrderItemId = null;
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
            $this->addError('keyAccessPassword', 'Sản phẩm này chưa có key được gắn vào.');

            return;
        }

        $this->revealedKeys[$orderItem->id] = [
            'keys'    => $keys,
            'visible' => true,
        ];

        if ($orderItem->buyer_key_viewed_at === null) {
            $orderItem->forceFill([
                'buyer_key_viewed_at' => now(),
            ])->save();
        }

        $this->keyAccessPassword = '';
        $this->keyAccessOrderItemId = null;
        session()->flash('library-status', 'Key của bạn đang hiển thị bên dưới. Hãy ở lại màn hình này trong lúc kiểm tra và kích hoạt.');
    }

    public function hideOrderItemKeys(int $orderItemId): void
    {
        if (! isset($this->revealedKeys[$orderItemId])) {
            return;
        }

        $this->revealedKeys[$orderItemId]['visible'] = false;
    }

    public function toggleOrderItemKeys(int $orderItemId): void
    {
        $orderItem = $this->resolveOwnedOrderItem($orderItemId);
        $keys = $this->orderItemKeys($orderItem);

        if ($keys === []) {
            return;
        }

        if ($orderItem->buyer_key_viewed_at === null) {
            $this->resetValidation('keyAccessPassword');
            $this->keyAccessPassword = '';
            $this->keyAccessOrderItemId = $orderItem->id;

            return;
        }

        $currentState = $this->revealedKeys[$orderItem->id] ?? [
            'keys'    => $keys,
            'visible' => false,
        ];

        $this->revealedKeys[$orderItem->id] = [
            'keys'    => $keys,
            'visible' => ! ($currentState['visible'] ?? false),
        ];
    }

    public function confirmReceived(int $orderItemId): void
    {
        $item = $this->resolveOwnedOrderItem($orderItemId);

        if ($item->status !== OrderStatus::Delivered || $item->buyer_key_viewed_at === null) {
            session()->flash('library-status', 'Vui lòng mở key một lần trước khi xác nhận đã nhận.');

            return;
        }

        $item->forceFill([
            'status' => OrderStatus::Completed,
        ])->save();

        $this->confirmReceivedOrderItemId = null;

        session()->flash('library-status', 'Sản phẩm trong đơn đã được đánh dấu hoàn tất.');
    }

    public function openReviewForm(int $orderItemId): void
    {
        $orderItem = $this->resolveOwnedOrderItem($orderItemId);

        if ($orderItem->status !== OrderStatus::Completed) {
            session()->flash('library-status', 'Chỉ sản phẩm đã hoàn tất mới có thể đánh giá.');

            return;
        }

        if ($orderItem->review !== null) {
            session()->flash('library-status', 'Bạn đã đánh giá sản phẩm này.');

            return;
        }

        $this->reviewOrderItemId = $orderItem->id;
        $this->reviewRating = '5';
        $this->reviewComment = '';
        $this->reviewMedia = [];
        $this->resetValidation('reviewRating');
        $this->resetValidation('reviewComment');
        $this->resetValidation('reviewMedia');
    }

    public function cancelReviewForm(): void
    {
        $this->reviewOrderItemId = null;
        $this->reviewRating = '5';
        $this->reviewComment = '';
        $this->reviewMedia = [];
        $this->resetValidation('reviewRating');
        $this->resetValidation('reviewComment');
        $this->resetValidation('reviewMedia');
    }

    public function submitReview(): void
    {
        $this->validate([
            'reviewRating'  => ['required', 'integer', 'between:1,5'],
            'reviewComment' => ['nullable', 'string', 'max:2000'],
            'reviewMedia'   => ['array', 'max:5'],
            'reviewMedia.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:12288'],
        ]);

        if ($this->reviewOrderItemId === null) {
            return;
        }

        $user = $this->resolveUser();

        $created = DB::transaction(function () use ($user): bool {
            $item = OrderItem::query()
                ->whereKey($this->reviewOrderItemId)
                ->whereHas('order', function ($query) use ($user): void {
                    $query->where('buyer_id', $user->id);
                })
                ->where('status', OrderStatus::Completed->value)
                ->lockForUpdate()
                ->firstOrFail();

            if ($item->review()->exists()) {
                return false;
            }

            Review::create([
                'user_id'       => $user->id,
                'order_item_id' => $item->id,
                'rating'        => (int) $this->reviewRating,
                'comment'       => filled(trim($this->reviewComment)) ? trim($this->reviewComment) : null,
                'media'         => $this->storeUploadedFiles($this->reviewMedia, 'reviews/media'),
            ]);

            return true;
        }, attempts: 3);

        if (! $created) {
            $this->cancelReviewForm();
            session()->flash('library-status', 'Bạn đã đánh giá sản phẩm này.');

            return;
        }

        $this->cancelReviewForm();

        session()->flash('library-status', 'Đánh giá của bạn đã được gửi.');
    }

    public function openConfirmReceivedModal(int $orderItemId): void
    {
        $orderItem = $this->resolveOwnedOrderItem($orderItemId);

        if ($orderItem->status !== OrderStatus::Delivered) {
            return;
        }

        $this->confirmReceivedOrderItemId = $orderItem->id;
    }

    public function cancelConfirmReceivedModal(): void
    {
        $this->confirmReceivedOrderItemId = null;
    }

    public function openComplaintForm(int $orderItemId): void
    {
        $orderItem = $this->resolveOwnedOrderItem($orderItemId);

        if (! in_array($orderItem->status, [OrderStatus::Delivered, OrderStatus::Disputing], true)) {
            session()->flash('library-status', 'Chỉ có thể mở khiếu nại cho sản phẩm đã giao hoặc đang khiếu nại.');

            return;
        }

        if ($orderItem->buyer_key_viewed_at === null) {
            session()->flash('library-status', 'Vui lòng mở key trước khi gửi khiếu nại.');

            return;
        }

        $this->complaintOrderItemId = $orderItem->id;
        $this->complaintReason = '';
        $this->complaintEvidence = [];
        $this->resetValidation('complaintReason');
    }

    public function cancelComplaintForm(): void
    {
        $this->complaintOrderItemId = null;
        $this->complaintReason = '';
        $this->complaintEvidence = [];
        $this->resetValidation('complaintReason');
    }

    public function openComplaintDetails(int $orderItemId): void
    {
        $orderItem = $this->resolveOwnedOrderItem($orderItemId);

        if ($orderItem->complaint === null) {
            return;
        }

        $this->viewingComplaintOrderItemId = $orderItem->id;
        $this->complaintReplyMessage = '';
        $this->complaintReplyAttachments = [];
        $this->resetValidation('complaintReplyMessage');
    }

    public function closeComplaintDetails(): void
    {
        $this->viewingComplaintOrderItemId = null;
        $this->complaintReplyMessage = '';
        $this->complaintReplyAttachments = [];
        $this->resetValidation('complaintReplyMessage');
    }

    public function submitComplaint(ComplaintService $complaintService): void
    {
        $this->validate([
            'complaintReason'     => ['required', 'string', 'min:10'],
            'complaintEvidence'   => ['required', 'array', 'min:1', 'max:3'],
            'complaintEvidence.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf,mp4,webm,mov', 'max:51200'],
        ]);

        $item = $this->resolveOwnedOrderItem((int) $this->complaintOrderItemId);

        if ($item->complaint()->exists()) {
            $this->cancelComplaintForm();

            return;
        }

        $complaint = $complaintService->openComplaint(
            $item,
            $this->resolveUser(),
            trim($this->complaintReason),
            $this->complaintEvidence,
        );

        $this->cancelComplaintForm();

        $this->redirectRoute('app.library.complaints.show', ['complaint' => $complaint->complaint_code]);
    }

    public function replyComplaint(ComplaintService $complaintService): void
    {
        $this->validate([
            'complaintReplyMessage'       => ['required', 'string', 'min:10'],
            'complaintReplyAttachments'   => ['array', 'max:5'],
            'complaintReplyAttachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf,mp4,webm,mov', 'max:51200'],
        ]);

        if ($this->viewingComplaintOrderItemId === null) {
            return;
        }

        $item = $this->resolveOwnedOrderItem($this->viewingComplaintOrderItemId);
        $complaint = $item->complaint()->with('messages')->first();

        if ($complaint === null) {
            return;
        }

        if (! in_array($complaint->status, [ComplaintStatus::Open, ComplaintStatus::InProcess, ComplaintStatus::Escalated], true)) {
            return;
        }

        $complaintService->reply(
            $complaint,
            $this->resolveUser(),
            trim($this->complaintReplyMessage),
            $this->complaintReplyAttachments,
        );

        $this->complaintReplyMessage = '';
        $this->complaintReplyAttachments = [];
        $this->resetValidation('complaintReplyMessage');
        session()->flash('library-status', 'Tin nhắn của bạn đã được thêm vào hội thoại khiếu nại.');
    }

    public function render(): View
    {
        $user = $this->resolveUser();
        $user->loadMissing('seller');

        $hasApprovedSellerAccount = $user->seller?->kyc_status === KycStatus::Approved;

        $orders = $user->orders()
            ->with([
                'items' => fn ($query) => $query
                    ->select(['id', 'order_id', 'listing_id', 'order_item_code', 'product_name_snapshot', 'quantity', 'unit_price', 'subtotal', 'status', 'buyer_key_viewed_at'])
                    ->with(['listing.variant.product', 'listing.variant.region', 'listing.variant.platform', 'listing.variant.operatingSystem', 'complaint.messages.sender', 'complaint.resolvedBy', 'review'])
                    ->withCount('keys')
                    ->orderBy('id'),
            ])
            ->withCount('items')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $selectedOrderItem = $this->viewingOrderItemId !== null
            ? $orders->flatMap(fn (Order $order): Collection => $order->items)->firstWhere('id', $this->viewingOrderItemId)
            : null;

        $selectedComplaintOrderItem = $this->viewingComplaintOrderItemId !== null
            ? $orders->flatMap(fn (Order $order): Collection => $order->items)->firstWhere('id', $this->viewingComplaintOrderItemId)
            : null;

        $selectedComplaintOrder = $selectedComplaintOrderItem !== null
            ? $orders->firstWhere('id', $selectedComplaintOrderItem->order_id)
            : null;

        $selectedComplaint = $selectedComplaintOrderItem?->complaint;

        $selectedReviewMedia = $selectedOrderItem?->review !== null
            ? $this->resolveStoredPaths($selectedOrderItem->review->media)
            : [];

        $selectedConfirmReceivedOrderItem = $this->confirmReceivedOrderItemId !== null
            ? $orders->flatMap(fn (Order $order): Collection => $order->items)->firstWhere('id', $this->confirmReceivedOrderItemId)
            : null;

        return view('pages.shop.library.my-library', [
            'user'                             => $user,
            'orders'                           => $orders,
            'pendingPaymentCount'              => $orders->where('payment_status', PaymentStatus::Pending)->count(),
            'completedOrderCount'              => $orders->filter(fn (Order $order): bool => $order->status === OrderStatus::Completed)->count(),
            'hasApprovedSellerAccount'         => $hasApprovedSellerAccount,
            'revealedKeys'                     => $this->revealedKeys,
            'selectedOrderItem'                => $selectedOrderItem,
            'selectedComplaintOrderItem'       => $selectedComplaintOrderItem,
            'selectedComplaintOrder'           => $selectedComplaintOrder,
            'selectedComplaint'                => $selectedComplaint,
            'selectedComplaintEvidence'        => $this->resolveStoredPaths($selectedComplaint?->evidence),
            'selectedComplaintMessages'        => $this->resolveComplaintMessages($selectedComplaint),
            'selectedReviewMedia'              => $selectedReviewMedia,
            'selectedConfirmReceivedOrderItem' => $selectedConfirmReceivedOrderItem,
        ])->layout('components.layouts.shop');
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    protected function resolveStoredPaths(?array $paths): array
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
    protected function resolveComplaintMessages(?Complaint $complaint): array
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
     * @param  array<int, mixed>  $files
     * @return list<string>
     */
    protected function storeUploadedFiles(array $files, string $directory): array
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
            ->with(['complaint', 'review'])
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
