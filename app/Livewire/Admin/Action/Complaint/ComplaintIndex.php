<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Complaint;

use App\Enums\ComplaintStatus;
use App\Enums\PaymentMethod;
use App\Managers\PaymentManager;
use App\Models\Complaint;
use App\Services\Shop\ComplaintService;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Title('Manage Complaints')]
class ComplaintIndex extends Component
{
    public bool $showViewModal = false;

    public ?array $viewData = null;

    public ?int $complaintId = null;

    public ?int $selectedStatus = null;

    public ?string $resolutionNote = null;

    public ?string $resolvedAt = null;

    private PaymentManager $paymentManager;

    public function boot(PaymentManager $paymentManager): void
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * @return array<int, ComplaintStatus>
     */
    private function updatableStatuses(): array
    {
        return [
            ComplaintStatus::Open,
            ComplaintStatus::InProcess,
            ComplaintStatus::Escalated,
        ];
    }

    public function render(): Factory|View|\Illuminate\View\View
    {
        return view('pages.admin.complaint.index')
            ->layout('components.layouts.dashboard');
    }

    #[On('openViewModal')]
    public function viewComplaint($id): void
    {
        $complaint = Complaint::with([
            'orderItem.order.buyer',
            'orderItem.listing.variant',
            'orderItem.listing.seller.user',
            'orderItem.escrow',
            'messages.sender',
        ])->find($id);

        if ($complaint) {
            $statusColorClass = match ($complaint->status) {
                ComplaintStatus::Open            => 'bg-blue-500/10 text-blue-500',
                ComplaintStatus::InProcess       => 'bg-yellow-500/10 text-yellow-500',
                ComplaintStatus::Escalated       => 'bg-orange-500/10 text-orange-500',
                ComplaintStatus::ApprovedRefund  => 'bg-green-500/10 text-green-500',
                ComplaintStatus::RejectedRelease => 'bg-gray-500/10 text-gray-500',
                default                          => 'bg-gray-500/10 text-gray-500',
            };

            $statusLabel = method_exists($complaint->status, 'label')
                ? $complaint->status->label()
                : $complaint->status->name;
            $statusBadge = '<span class="'.$statusColorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$statusLabel.'</span>';

            $this->viewData = [
                'complaint_id'    => $complaint->id,
                'thread_url'      => url('/admin/complaints/'.($complaint->complaint_code ?: $complaint->id)),
                'order_code'      => $complaint->orderItem?->order?->order_code ?? '-',
                'buyer_username'  => $complaint->orderItem?->order?->buyer?->username ?? '-',
                'buyer_email'     => $complaint->orderItem?->order?->buyer?->email ?? '-',
                'seller_username' => $complaint->orderItem?->listing?->seller?->user?->username ?? __('admin.common.shop_admin'),
                'seller_email'    => $complaint->orderItem?->listing?->seller?->user?->email ?? 'KeyCove',
                'product_name'    => $complaint->orderItem?->product_name_snapshot ?? '-',
                'reason'          => $complaint->reason,
                'escrow_status'   => $complaint->orderItem?->escrow?->status?->label() ?? '-',
                'evidence'        => $this->resolveStoredPaths($complaint->evidence),
                'status'          => $complaint->status->value,
                'status_badge'    => $statusBadge,
                'messages'        => $complaint->messages()
                    ->with('sender')
                    ->orderBy('created_at')
                    ->get()
                    ->map(fn ($msg) => [
                        'id'          => $msg->id,
                        'sender_name' => $msg->sender?->username ?? __('admin.common.unknown'),
                        'message'     => $msg->message,
                        'attachments' => $this->resolveStoredPaths($msg->attachments),
                        'created_at'  => $msg->created_at->format('d/m/Y H:i:s'),
                    ])
                    ->toArray(),
                'message_count' => $complaint->messages()->count(),
            ];

            $this->selectedStatus = $complaint->status->value;
            $this->resolutionNote = $complaint->resolution_note;
            $this->resolvedAt = $complaint->resolved_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
            $this->complaintId = $complaint->id;

            $this->showViewModal = true;
        }
    }

    public function updateStatus(): void
    {
        if ($this->selectedStatus === null || ! in_array($this->selectedStatus, array_map(fn (ComplaintStatus $status) => $status->value, $this->updatableStatuses()), true)) {
            return;
        }

        if ($this->complaintId === null) {
            return;
        }

        $complaint = Complaint::find($this->complaintId);

        if ($complaint) {
            $complaint->update(['status' => $this->selectedStatus]);
            $this->showViewModal = false;
            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => __('admin.messages.updated', ['Name' => __('admin.nav.dispute_center')]),
            ]);

            $this->dispatch('pg:eventRefresh-complaintTable');
        }
    }

    public function processRefund(ComplaintService $complaintService): void
    {
        $this->validate([
            'resolutionNote' => ['required', 'string', 'min:3'],
            'resolvedAt'     => ['required', 'date'],
        ]);

        if ($this->selectedStatus !== ComplaintStatus::ApprovedRefund->value) {
            return;
        }

        if ($this->complaintId === null) {
            return;
        }

        $complaint = Complaint::with(['orderItem.order.transaction', 'orderItem.escrow', 'orderItem.order.buyer', 'orderItem.seller.user'])->find($this->complaintId);

        if (! $complaint) {
            return;
        }

        $transaction = $complaint->orderItem?->order?->transaction;
        $paymentMethod = $complaint->orderItem?->order?->payment_method;

        if ($paymentMethod !== PaymentMethod::VNPay) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => __('admin.messages.refund_vnpay_only'),
            ]);

            return;
        }

        if (! $transaction) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => __('admin.messages.refund_payment_not_found'),
            ]);

            return;
        }

        try {
            // TODO: refund stuff
            //            $refundResponse = $this->paymentManager->driver('vnpay')->refund([
            //                'txn_ref'          => $complaint->orderItem?->order?->order_code ?? (string) $complaint->id,
            //                'amount'           => (float) $complaint->orderItem?->subtotal,
            //                'order_info'       => 'Refund complaint #'.$complaint->id,
            //                'transaction_no'   => $transaction->payment_info['transaction_id'] ?? null,
            //                'transaction_date' => $transaction->payment_info['pay_date'] ?? now()->format('YmdHis'),
            //                'create_by'        => (string) auth()->id(),
            //                'ip_address'       => request()->ip(),
            //            ]);
            //
            //            if (! ($refundResponse['success'] ?? false)) {
            //                $message = $refundResponse['message'] ?? 'Refund request was rejected by the payment gateway.';
            //
            //                $this->dispatch('notify', [
            //                    'type'    => 'error',
            //                    'message' => $message,
            //                ]);
            //
            //                return;
            //            }

            $complaintService->resolveRefund(
                $complaint,
                auth()->guard('admin')->user() ?? auth()->user(),
                (string) $this->resolutionNote,
                $this->resolvedAt,
            );

            $this->showViewModal = false;
            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => __('admin.messages.refund_processed'),
            ]);
            $this->dispatch('pg:eventRefresh-complaintTable');
        } catch (Throwable $throwable) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => __('admin.messages.refund_failed', ['error' => $throwable->getMessage()]),
            ]);
        }
    }

    public function processRelease(ComplaintService $complaintService): void
    {
        $this->validate([
            'resolutionNote' => ['required', 'string', 'min:3'],
            'resolvedAt'     => ['required', 'date'],
        ]);

        if ($this->selectedStatus !== ComplaintStatus::RejectedRelease->value) {
            return;
        }

        if ($this->complaintId === null) {
            return;
        }

        $complaint = Complaint::with(['orderItem.escrow', 'orderItem.order.buyer', 'orderItem.seller.user'])->find($this->complaintId);

        if (! $complaint) {
            return;
        }

        // TODO: release stuff

        $complaintService->resolveRelease(
            $complaint,
            auth()->guard('admin')->user() ?? auth()->user(),
            (string) $this->resolutionNote,
            $this->resolvedAt,
        );

        $this->showViewModal = false;
        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => __('admin.messages.release_processed'),
        ]);
        $this->dispatch('pg:eventRefresh-complaintTable');
    }

    private function resolveStoredPaths(?array $paths): array
    {
        return collect($paths ?? [])
            ->filter(fn ($path): bool => is_string($path) && $path !== '')
            ->map(fn (string $path): array => [
                'label' => $path,
                'url'   => $this->resolveDisplayUrl($path),
            ])
            ->values()
            ->all();
    }

    private function resolveDisplayUrl(string $value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
            return $value;
        }

        if (! $this->looksLikeStoragePath($value)) {
            return null;
        }

        try {
            return StorageUtility::getUrl($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function looksLikeStoragePath(string $value): bool
    {
        if (str_contains($value, '/')) {
            return true;
        }

        return (bool) preg_match('/\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|rar|txt|doc|docx|xls|xlsx)$/i', $value);
    }
}
