<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Library;

use App\Enums\ComplaintStatus;
use App\Enums\OrderStatus;
use App\Managers\PaymentManager;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Shop\ComplaintService;
use App\Services\Shop\MyLibraryService;
use App\Services\Shop\OrderItemCompletionService;
use App\Services\Shop\OrderReviewService;
use App\Services\Shop\PendingOrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
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

    protected MyLibraryService $myLibraryService;

    public function boot(MyLibraryService $myLibraryService): void
    {
        $this->myLibraryService = $myLibraryService;
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

        $keyState = $this->myLibraryService->revealedKeyState($orderItem);

        if ($keyState === null) {
            return;
        }

        if ($orderItem->buyer_key_viewed_at !== null) {
            $this->revealedKeys[$orderItem->id] = $keyState;
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

        $keyState = $this->myLibraryService->revealedKeyState($orderItem);

        if ($keyState === null) {
            $this->addError('keyAccessPassword', 'Sản phẩm này chưa có key được gắn vào.');

            return;
        }

        $this->revealedKeys[$orderItem->id] = $keyState;
        $this->myLibraryService->markKeysViewed($orderItem);

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
        $keyState = $this->myLibraryService->revealedKeyState($orderItem, false);

        if ($orderItem->buyer_key_viewed_at === null) {
            $this->resetValidation('keyAccessPassword');
            $this->keyAccessPassword = '';
            $this->keyAccessOrderItemId = $orderItem->id;

            return;
        }

        if ($keyState === null) {
            return;
        }

        $currentState = $this->revealedKeys[$orderItem->id] ?? $keyState;

        $this->revealedKeys[$orderItem->id] = [
            'keys'    => $currentState['keys'],
            'visible' => ! ($currentState['visible'] ?? false),
        ];
    }

    public function confirmReceived(int $orderItemId, OrderItemCompletionService $orderItemCompletionService): void
    {
        $item = $this->resolveOwnedOrderItem($orderItemId);

        if ($item->status !== OrderStatus::Delivered || $item->buyer_key_viewed_at === null) {
            session()->flash('library-status', 'Vui lòng mở key một lần trước khi xác nhận đã nhận.');

            return;
        }

        if (! $orderItemCompletionService->complete($item->id, 'buyer_confirmed')) {
            session()->flash('library-status', 'Không thể hoàn tất sản phẩm này vì đang có khiếu nại hoặc escrow không còn sẵn sàng giải ngân.');

            return;
        }

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

    public function submitReview(OrderReviewService $orderReviewService): void
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

        $created = $orderReviewService->submit(
            $this->resolveUser(),
            $this->reviewOrderItemId,
            (int) $this->reviewRating,
            $this->reviewComment,
            $this->reviewMedia,
        );

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
        return view('pages.shop.library.my-library', $this->myLibraryService->pageData(
            $this->resolveUser(),
            $this->revealedKeys,
            $this->viewingOrderItemId,
            $this->viewingComplaintOrderItemId,
            $this->confirmReceivedOrderItemId,
        ))->layout('components.layouts.shop');
    }

    protected function resolveUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    protected function resolveOwnedOrder(int $orderId): Order
    {
        return $this->myLibraryService->resolveOwnedOrder($this->resolveUser(), $orderId);
    }

    protected function resolveOwnedOrderItem(int $orderItemId): OrderItem
    {
        return $this->myLibraryService->resolveOwnedOrderItem($this->resolveUser(), $orderItemId);
    }

    protected function canRevealKeys(OrderItem $orderItem): bool
    {
        return $this->myLibraryService->canRevealKeys($orderItem);
    }

    /**
     * @return list<string>
     */
    protected function orderItemKeys(OrderItem $orderItem): array
    {
        return $this->myLibraryService->orderItemKeys($orderItem);
    }
}
