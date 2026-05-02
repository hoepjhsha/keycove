<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\InternalWallet;

use App\Enums\EscrowStatus;
use App\Enums\TransactionStatus;
use App\Enums\WalletType;
use App\Enums\WithdrawStatus;
use App\Models\Escrow;
use App\Models\InternalWalletEntry;
use App\Models\Wallet;
use App\Models\Withdraw;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Internal Wallet')]
class InternalWalletIndex extends Component
{
    public bool $showViewModal = false;

    public ?array $viewData = null;

    public function render(): View
    {
        return view('pages.admin.internal-wallet.index', [
            'metrics' => $this->metrics(),
        ])->layout('components.layouts.dashboard');
    }

    #[On('viewInternalWalletEntryModal')]
    public function viewEntry(int $rowId): void
    {
        $entry = InternalWalletEntry::query()
            ->with(['order.buyer', 'wallet', 'source'])
            ->find($rowId);

        if ($entry === null) {
            return;
        }

        $typeLabel = method_exists($entry->type, 'label') ? $entry->type->label() : $entry->type->name;
        $statusLabel = method_exists($entry->status, 'label') ? $entry->status->label() : $entry->status->name;
        $directionLabel = method_exists($entry->direction, 'label') ? $entry->direction->label() : $entry->direction->name;

        $typeHtml = sprintf(
            '<span class="%s text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">%s</span>',
            $this->typeColorClass($entry->type->name),
            $typeLabel,
        );

        $statusHtml = sprintf(
            '<span class="%s text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">%s</span>',
            $this->statusColorClass($entry->status),
            $statusLabel,
        );

        $this->viewData = [
            'id'               => $entry->id,
            'order_code'       => $entry->order?->order_code ?? '-',
            'buyer_username'   => $entry->order?->buyer?->username ?? '-',
            'wallet_code'      => $entry->wallet?->code ?? '-',
            'type_label'       => $typeHtml,
            'status_label'     => $statusHtml,
            'direction_label'  => $directionLabel,
            'amount'           => $this->formatSignedAmount($entry),
            'affects_balance'  => $entry->affects_balance ? __('admin.common.yes') : __('admin.common.no'),
            'source_reference' => $entry->source_type !== null ? class_basename($entry->source_type).' #'.$entry->source_id : '-',
            'occurred_at'      => $entry->occurred_at?->format('d/m/Y H:i:s') ?? '-',
            'created_at'       => $entry->created_at?->format('d/m/Y H:i:s') ?? '-',
            'metadata'         => $entry->metadata ?? [],
        ];

        $this->showViewModal = true;
    }

    /**
     * @return array<string, float>
     */
    protected function metrics(): array
    {
        $cashIn = (float) InternalWalletEntry::query()
            ->where('affects_balance', true)
            ->where('status', TransactionStatus::Completed)
            ->where('direction', 0)
            ->sum('amount');

        $cashOut = (float) InternalWalletEntry::query()
            ->where('affects_balance', true)
            ->where('status', TransactionStatus::Completed)
            ->where('direction', 1)
            ->sum('amount');

        $walletBalance = (float) Wallet::query()
            ->where('type', WalletType::Internal)
            ->value('balance');

        $sellerAvailableLiability = (float) Wallet::query()
            ->where('type', WalletType::Seller)
            ->sum('balance');

        $sellerHoldingLiability = (float) Escrow::query()
            ->whereIn('status', [EscrowStatus::Holding, EscrowStatus::Frozen])
            ->sum('amount');

        $pendingWithdrawalLiability = (float) Withdraw::query()
            ->whereIn('status', [WithdrawStatus::Pending, WithdrawStatus::Processing])
            ->sum('amount');

        return [
            'cashIn'                     => $cashIn,
            'cashOut'                    => $cashOut,
            'netBalance'                 => $walletBalance,
            'sellerAvailableLiability'   => $sellerAvailableLiability,
            'sellerHoldingLiability'     => $sellerHoldingLiability,
            'pendingWithdrawalLiability' => $pendingWithdrawalLiability,
        ];
    }

    protected function formatSignedAmount(InternalWalletEntry $entry): string
    {
        $prefix = match ($entry->direction->name) {
            'Inflow'  => '+',
            'Outflow' => '-',
            default   => '',
        };

        return $prefix.number_format((float) $entry->amount, 2).' VND';
    }

    protected function typeColorClass(string $typeName): string
    {
        return match ($typeName) {
            'PaymentReceived' => 'bg-emerald-500/10 text-emerald-500',
            'RefundPaid', 'SellerPayoutFailed' => 'bg-rose-500/10 text-rose-500',
            'SellerPayoutRequested', 'SellerPayoutCompleted' => 'bg-indigo-500/10 text-indigo-500',
            'EscrowHeld', 'EscrowReleased' => 'bg-sky-500/10 text-sky-500',
            default => 'bg-slate-500/10 text-slate-500',
        };
    }

    protected function statusColorClass(TransactionStatus $status): string
    {
        return match ($status) {
            TransactionStatus::Pending   => 'bg-amber-500/10 text-amber-500',
            TransactionStatus::Completed => 'bg-emerald-500/10 text-emerald-500',
            TransactionStatus::Failed    => 'bg-rose-500/10 text-rose-500',
            TransactionStatus::Cancelled => 'bg-slate-500/10 text-slate-500',
            default                      => 'bg-slate-500/10 text-slate-500',
        };
    }
}
