<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Escrow;

use App\Constants\Admin\EscrowConstant;
use App\Enums\EscrowStatus;
use App\Livewire\Admin\Form\Escrow\EscrowExtendHoldingForm;
use App\Models\Escrow;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manage Escrows')]
class EscrowIndex extends Component
{
    public bool $showViewModal = false;

    public ?array $viewData = null;

    public bool $showExtendModal = false;

    public ?int $extendEscrowId = null;

    public EscrowExtendHoldingForm $extendExtendHoldingForm;

    public function render(): Factory|View|\Illuminate\View\View
    {
        return view('pages.admin.escrow.index')
            ->layout('components.layouts.dashboard');
    }

    #[On('viewEscrowDetail')]
    public function viewEscrow($rowId): void
    {
        $escrow = Escrow::with(['orderItem.order.buyer', 'seller'])->find($rowId);

        if ($escrow) {
            $statusColorClass = match ($escrow->status) {
                EscrowStatus::Holding  => 'bg-blue-500/10 text-blue-500',
                EscrowStatus::Released => 'bg-green-500/10 text-green-500',
                EscrowStatus::Refunded => 'bg-red-500/10 text-red-500',
                EscrowStatus::Frozen   => 'bg-purple-500/10 text-purple-500',
                default                => 'bg-gray-500/10 text-gray-500',
            };

            $statusLabel = method_exists($escrow->status, 'label')
                ? $escrow->status->label()
                : $escrow->status->name;
            $statusBadge = '<span class="'.$statusColorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$statusLabel.'</span>';

            $this->viewData = [
                'id'               => $escrow->id,
                'order_code'       => $escrow->orderItem?->order?->order_code ?? '-',
                'buyer_username'   => $escrow->orderItem?->order?->buyer?->username ?? '-',
                'buyer_email'      => $escrow->orderItem?->order?->buyer?->email ?? '-',
                'seller_shop_name' => $escrow->seller?->shop_name ?? 'Shop Admin',
                'amount'           => number_format((float) $escrow->amount, 2).' VND',
                'status_badge'     => $statusBadge,
                'release_date'     => $escrow->release_date?->format('d/m/Y H:i:s') ?? '-',
                'created_at'       => $escrow->created_at->format('d/m/Y H:i:s'),
                'updated_at'       => $escrow->updated_at->format('d/m/Y H:i:s'),
            ];

            $this->showViewModal = true;
        }
    }

    #[On('openExtendModal')]
    public function openExtendModal($id): void
    {
        $escrow = Escrow::findOrFail($id);

        if ($escrow->status !== EscrowStatus::Holding && $escrow->status !== EscrowStatus::Frozen) {
            $this->dispatch('swal:error', [
                'message' => 'Only escrows with Holding or Frozen status can be extended.',
            ]);

            return;
        }

        $maxAllowedDate = $escrow->created_at->copy()->addDays(EscrowConstant::MAX_EXTEND_DAYS_FROM_CREATED);
        if ($escrow->release_date->greaterThanOrEqualTo($maxAllowedDate)) {
            $this->dispatch('swal:error', [
                'message' => 'This escrow has reached the maximum extension limit.',
            ]);

            return;
        }

        $this->extendEscrowId = $id;
        $this->extendExtendHoldingForm->duration = '';
        $this->showExtendModal = true;
    }

    public function performExtendHolding(): void
    {
        try {
            $this->extendExtendHoldingForm->submit($this->extendEscrowId);

            $this->dispatch('swal:success', [
                'message' => 'Escrow holding time extended successfully by '.$this->extendExtendHoldingForm->duration.' day(s).',
            ]);

            $this->showExtendModal = false;
            $this->extendEscrowId = null;
            $this->extendExtendHoldingForm->duration = '';
        } catch (Exception $e) {
            $this->dispatch('swal:error', [
                'message' => 'Failed to extend escrow: '.$e->getMessage(),
            ]);
        }
    }
}
