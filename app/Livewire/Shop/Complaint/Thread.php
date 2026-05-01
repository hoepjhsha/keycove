<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Complaint;

use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\User;
use App\Services\Shop\ComplaintService;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Hội thoại khiếu nại')]
class Thread extends Component
{
    use WithFileUploads;

    /**
     * @var Complaint
     */
    public $complaint;

    public string $replyMessage = '';

    /**
     * @var array<int, mixed>
     */
    public array $replyAttachments = [];

    public string $resolutionNote = '';

    public function mount(mixed $complaint): void
    {
        $this->complaint = $complaint instanceof Complaint
            ? $complaint
            : Complaint::query()
                ->where('complaint_code', $complaint)
                ->orWhere('id', $complaint)
                ->firstOrFail();

        $this->complaint->load([
            'orderItem.order.buyer',
            'orderItem.seller.user',
            'orderItem.listing.variant.product',
            'orderItem.listing.variant.region',
            'orderItem.listing.variant.platform',
            'orderItem.listing.variant.operatingSystem',
            'messages.sender',
            'resolvedBy',
        ]);

        $this->authorizeComplaint('view');
    }

    public function reply(ComplaintService $complaintService): void
    {
        $this->authorizeComplaint('reply');

        $this->validate([
            'replyMessage'       => ['required', 'string', 'min:10'],
            'replyAttachments'   => ['array', 'max:5'],
            'replyAttachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf,mp4,webm,mov', 'max:51200'],
        ]);

        $complaintService->reply(
            $this->complaint,
            $this->currentUser(),
            trim($this->replyMessage),
            $this->replyAttachments,
        );

        $this->replyMessage = '';
        $this->replyAttachments = [];
        $this->refreshComplaint();

        session()->flash('complaint-status', 'Tin nhắn của bạn đã được thêm vào hội thoại khiếu nại.');
    }

    public function refundBuyer(ComplaintService $complaintService): void
    {
        $this->authorizeComplaint('resolve');

        $this->validate([
            'resolutionNote' => ['required', 'string', 'min:3'],
        ]);

        $complaintService->refundComplaint(
            $this->complaint,
            $this->currentUser(),
            trim($this->resolutionNote),
        );

        $this->refreshComplaint();
        session()->flash('complaint-status', 'Khiếu nại đã được đánh dấu chấp thuận hoàn tiền.');
    }

    public function releaseFunds(ComplaintService $complaintService): void
    {
        $this->authorizeComplaint('resolve');

        $this->validate([
            'resolutionNote' => ['required', 'string', 'min:3'],
        ]);

        $complaintService->resolveRelease(
            $this->complaint,
            $this->currentUser(),
            trim($this->resolutionNote),
        );

        $this->refreshComplaint();
        session()->flash('complaint-status', 'Khiếu nại đã được đánh dấu đã xử lý và tiền đã được giải phóng.');
    }

    #[On('echo-private:complaints.{complaint.id},ComplaintThreadUpdated')]
    public function refreshFromBroadcast(): void
    {
        $this->refreshComplaint();
    }

    public function render(): View
    {
        $user = $this->currentUser();
        $this->refreshComplaint();

        $layout = $this->resolveLayout($user);
        $layoutData = $this->resolveLayoutData($user);

        return view('pages.shop.complaint.thread', [
            'user'            => $user,
            'complaint'       => $this->complaint,
            'messages'        => $this->threadMessages(),
            'canReply'        => Gate::forUser($user)->allows('reply', $this->complaint),
            'canResolve'      => Gate::forUser($user)->allows('resolve', $this->complaint),
            'isAdmin'         => $this->isAdmin($user),
            'isSellerOwned'   => $this->isSellerOwnedComplaint(),
            'backUrl'         => $this->backUrl(),
            'participantName' => $this->participantLabel(),
        ])->layout($layout, $layoutData);
    }

    protected function refreshComplaint(): void
    {
        $this->complaint->refresh()->load([
            'orderItem.order.buyer',
            'orderItem.seller.user',
            'orderItem.listing.variant.product',
            'orderItem.listing.variant.region',
            'orderItem.listing.variant.platform',
            'orderItem.listing.variant.operatingSystem',
            'messages.sender',
            'resolvedBy',
        ]);
    }

    /**
     * @return array<int, array{id: int, sender_id: int|null, sender_name: string, sender_role: string, message: string, created_at: string, attachments: array<int, array{label: string, url: ?string}>}>
     */
    protected function threadMessages(): array
    {
        return $this->complaint->messages
            ->map(function ($message): array {
                return [
                    'id'          => $message->id,
                    'sender_id'   => $message->sender?->id,
                    'sender_name' => $message->sender?->username ?? 'Hỗ trợ',
                    'sender_role' => $message->sender?->role?->label() ?? 'Không xác định',
                    'message'     => $message->message,
                    'created_at'  => $message->created_at?->format('d/m/Y H:i') ?? '--',
                    'attachments' => $this->formatAttachments($message->attachments),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    protected function formatAttachments(?array $paths): array
    {
        return collect($paths ?? [])
            ->filter(fn ($path): bool => is_string($path) && $path !== '')
            ->map(fn (string $path): array => [
                'label' => basename($path),
                'url'   => StorageUtility::getUrl($path),
            ])
            ->values()
            ->all();
    }

    protected function authorizeComplaint(string $ability): void
    {
        Gate::forUser($this->currentUser())->authorize($ability, $this->complaint);
    }

    protected function currentUser(): User
    {
        $user = auth()->guard('admin')->user() ?? auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    protected function isAdmin(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin], true);
    }

    protected function isSellerOwnedComplaint(): bool
    {
        return $this->complaint->orderItem?->seller_id !== null;
    }

    protected function participantLabel(): string
    {
        return $this->isSellerOwnedComplaint() ? 'Buyer, seller, and admin' : 'Buyer and admin';
    }

    protected function resolveLayout(User $user): string
    {
        if (request()->routeIs('admin.complaints.show')) {
            return 'components.layouts.dashboard';
        }

        if (request()->routeIs('seller.complaints.show')) {
            return 'components.layouts.seller';
        }

        return 'components.layouts.shop';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveLayoutData(User $user): array
    {
        if (request()->routeIs('admin.complaints.show')) {
            return [];
        }

        if (request()->routeIs('seller.complaints.show')) {
            $seller = $user->loadMissing('seller')->seller;

            return [
                'title'         => 'Complaint Thread',
                'user'          => $user,
                'seller'        => $seller,
                'activeSection' => 'complaints',
            ];
        }

        return [];
    }

    protected function backUrl(): string
    {
        $user = $this->currentUser();

        if ($this->isAdmin($user)) {
            return '/admin/complaints';
        }

        if ($this->isSellerOwnedComplaint() || $user->role === UserRole::Seller) {
            return '/seller/complaints';
        }

        return '/my-library';
    }
}
