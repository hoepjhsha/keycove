<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Transaction;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TransactionIndex extends Component
{
    public bool $showViewModal = false;

    public ?array $viewData = null;

    #[On('viewTransactionModal')]
    public function viewTransaction($rowId): void
    {
        $transaction = Transaction::with(['order.buyer', 'wallet.seller'])->find($rowId);

        if ($transaction) {
            // Format Transaction Type
            $typeLabel = method_exists($transaction->type, 'label') ? $transaction->type->label() : $transaction->type->name;
            $typeClass = match ($transaction->type) {
                TransactionType::PaymentReceived => 'bg-blue-500/10 text-blue-500',
                TransactionType::EscrowHold,
                TransactionType::EscrowRelease => 'bg-emerald-500/10 text-emerald-500',
                TransactionType::Withdraw,
                TransactionType::WithdrawReserve,
                TransactionType::WithdrawRelease => 'bg-indigo-500/10 text-indigo-500',
                TransactionType::Refund          => 'bg-red-500/10 text-red-500',
                default                          => 'bg-gray-500/10 text-gray-500',
            };
            $typeHtml = '<span class="'.$typeClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$typeLabel.'</span>';

            // Format Transaction Status
            $statusLabel = method_exists($transaction->status, 'label') ? $transaction->status->label() : $transaction->status->name;
            $statusClass = match ($transaction->status) {
                TransactionStatus::Pending   => 'bg-yellow-500/10 text-yellow-500',
                TransactionStatus::Completed => 'bg-green-500/10 text-green-500',
                TransactionStatus::Failed    => 'bg-red-500/10 text-red-500',
                TransactionStatus::Cancelled => 'bg-gray-500/10 text-gray-500',
                default                      => 'bg-primary-500/10 text-primary-500',
            };
            $statusHtml = '<span class="'.$statusClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$statusLabel.'</span>';

            $this->viewData = [
                'id'           => $transaction->id,
                'order_code'   => $transaction->order?->order_code ?? '-',
                'username'     => $transaction->order?->buyer?->username ?? '-',
                'type_label'   => $typeHtml,
                'amount'       => $transaction->amount.' VND',
                'status_label' => $statusHtml,
                'payment_info' => $transaction->payment_info ?? [],
                'created_at'   => $transaction->created_at->format('d/m/Y H:i:s'),
            ];

            $this->showViewModal = true;
        }
    }

    public function render(): Factory|View|\Illuminate\View\View
    {
        return view('pages.admin.transaction.index')
            ->layout('components.layouts.dashboard');
    }
}
