<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Withdraw;

use App\Enums\UserRole;
use App\Enums\WithdrawStatus;
use App\Models\User;
use App\Models\Withdraw;
use App\Services\Shop\SellerWithdrawalService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Title('Withdrawal Requests')]
class WithdrawalRequestIndex extends Component
{
    public bool $showViewModal = false;

    public ?int $rejectingWithdrawId = null;

    public bool $showRejectModal = false;

    public string $rejectReason = '';

    public ?array $viewData = null;

    public function render(): View
    {
        return view('pages.admin.withdrawal-requests.index', [
            'withdrawals' => $this->withdrawals(),
        ])->layout('components.layouts.dashboard');
    }

    public function approve(int $withdrawId, SellerWithdrawalService $service): void
    {
        try {
            $service->approveWithdrawal($this->findWithdrawal($withdrawId), $this->adminUser());

            $this->dispatch('swal:success', [
                'message' => __('admin.messages.withdrawal_processed'),
            ]);
        } catch (Throwable $throwable) {
            if ($throwable instanceof ValidationException) {
                $this->dispatch('swal:error', [
                    'message' => $throwable->getMessage(),
                ]);

                return;
            }

            report($throwable);

            $this->dispatch('swal:error', [
                'message' => __('admin.messages.withdrawal_process_failed', ['error' => $throwable->getMessage()]),
            ]);
        }
    }

    public function viewWithdrawal(int $withdrawId): void
    {
        $withdrawal = $this->withdrawals()->firstWhere('id', $withdrawId)
            ?? $this->findWithdrawal($withdrawId);

        $seller = $withdrawal->wallet?->seller;
        $statusLabel = $withdrawal->status->label();
        $statusClass = match ($withdrawal->status) {
            WithdrawStatus::Completed  => 'bg-emerald-500/10 text-emerald-600',
            WithdrawStatus::Pending    => 'bg-amber-500/10 text-amber-600',
            WithdrawStatus::Processing => 'bg-sky-500/10 text-sky-600',
            WithdrawStatus::Rejected, WithdrawStatus::Failed => 'bg-rose-500/10 text-rose-600',
            default => 'bg-slate-500/10 text-slate-600',
        };

        $this->viewData = [
            'id'             => $withdrawal->id,
            'seller_name'    => $seller?->shop_name ?? $seller?->user?->username ?? __('admin.common.unknown'),
            'requested_by'   => $withdrawal->requestedBy?->username ?? __('admin.common.unknown'),
            'processed_by'   => $withdrawal->processedBy?->username ?? '-',
            'bank_name'      => $withdrawal->bank_name,
            'bank_code'      => (string) ($withdrawal->metadata['bank_code'] ?? '-'),
            'account_name'   => $withdrawal->bank_account_name,
            'account_number' => $this->maskAccountNumber((string) $withdrawal->bank_account_number),
            'amount'         => number_format((float) $withdrawal->amount, 2).' VND',
            'status_label'   => sprintf(
                '<span class="%s text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">%s</span>',
                $statusClass,
                $statusLabel,
            ),
            'reject_reason' => $withdrawal->reject_reason ?? '-',
            'processed_at'  => $withdrawal->processed_at?->format('d/m/Y H:i:s') ?? '-',
            'created_at'    => $withdrawal->created_at?->format('d/m/Y H:i:s') ?? '-',
            'metadata'      => $withdrawal->metadata ?? [],
        ];

        $this->showViewModal = true;
    }

    public function openRejectModal(int $withdrawId): void
    {
        $this->rejectingWithdrawId = $withdrawId;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function reject(SellerWithdrawalService $service): void
    {
        $validated = $this->validate([
            'rejectReason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $service->rejectWithdrawal(
                $this->findWithdrawal((int) $this->rejectingWithdrawId),
                $this->adminUser(),
                $validated['rejectReason'],
            );

            $this->dispatch('swal:success', [
                'message' => __('admin.messages.withdrawal_rejected'),
            ]);

            $this->showRejectModal = false;
            $this->rejectingWithdrawId = null;
            $this->rejectReason = '';
        } catch (Throwable $throwable) {
            if ($throwable instanceof ValidationException) {
                throw $throwable;
            }

            report($throwable);

            $this->dispatch('swal:error', [
                'message' => __('admin.messages.withdrawal_reject_failed', ['error' => $throwable->getMessage()]),
            ]);
        }
    }

    /**
     * @return Collection<int, Withdraw>
     */
    protected function withdrawals(): Collection
    {
        return Withdraw::query()
            ->with(['wallet.seller.user', 'requestedBy', 'processedBy'])
            ->orderByRaw('CASE WHEN status IN (?, ?) THEN 0 ELSE 1 END', [
                WithdrawStatus::Pending->value,
                WithdrawStatus::Processing->value,
            ])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();
    }

    protected function adminUser(): User
    {
        $user = auth('admin')->user();

        abort_unless($user instanceof User, 403);
        abort_unless(in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin], true), 403);

        return $user;
    }

    protected function findWithdrawal(int $withdrawId): Withdraw
    {
        return Withdraw::query()
            ->with(['wallet.seller.user', 'requestedBy', 'processedBy'])
            ->findOrFail($withdrawId);
    }

    protected function maskAccountNumber(string $accountNumber): string
    {
        $length = strlen($accountNumber);

        if ($length <= 4) {
            return $accountNumber;
        }

        return str_repeat('*', $length - 4).substr($accountNumber, -4);
    }
}
