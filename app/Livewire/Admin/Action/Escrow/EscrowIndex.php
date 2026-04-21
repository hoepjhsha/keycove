<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Escrow;

use App\Enums\AuditEvent;
use App\Enums\EscrowStatus;
use App\Models\AuditLog;
use App\Models\Escrow;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Manage Escrows')]
class EscrowIndex extends Component
{
    public bool $showViewModal = false;

    public ?array $viewData = null;

    public bool $showExtendModal = false;

    public ?int $extendEscrowId = null;

    #[Validate('required|numeric|min:1|max:2')]
    public ?int $extendDays = null;

    private const MAX_EXTEND_DAYS_FROM_CREATED = 90; // Maximum 90 days from created_at

    public function render(): Factory|View|\Illuminate\View\View
    {
        return view('pages.admin.escrow.index')
            ->layout('components.layouts.dashboard');
    }

    #[On('viewEscrowDetail')]
    public function viewEscrow($rowId): void
    {
        $escrow = Escrow::with(['order', 'order.buyer', 'seller'])->find($rowId);

        if ($escrow) {
            $statusColorClass = match ($escrow->status) {
                EscrowStatus::Holding => 'bg-blue-500/10 text-blue-500',
                EscrowStatus::Released => 'bg-green-500/10 text-green-500',
                EscrowStatus::Refunded => 'bg-red-500/10 text-red-500',
                EscrowStatus::Frozen => 'bg-purple-500/10 text-purple-500',
                default => 'bg-gray-500/10 text-gray-500',
            };

            $statusLabel = method_exists($escrow->status, 'label')
                ? $escrow->status->label()
                : $escrow->status->name;
            $statusBadge = '<span class="'.$statusColorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$statusLabel.'</span>';

            $this->viewData = [
                'id' => $escrow->id,
                'order_code' => $escrow->order?->order_code ?? '-',
                'buyer_username' => $escrow->order?->buyer?->username ?? '-',
                'buyer_email' => $escrow->order?->buyer?->email ?? '-',
                'seller_shop_name' => $escrow->seller?->shop_name ?? '-',
                'amount' => number_format((float) $escrow->amount, 2).' VND',
                'status_badge' => $statusBadge,
                'release_date' => $escrow->release_date?->format('d/m/Y H:i:s') ?? '-',
                'created_at' => $escrow->created_at->format('d/m/Y H:i:s'),
                'updated_at' => $escrow->updated_at->format('d/m/Y H:i:s'),
            ];

            $this->showViewModal = true;
        }
    }

    #[On('openExtendModal')]
    public function openExtendModal($id): void
    {
        $escrow = Escrow::findOrFail($id);

        // Validate can extend
        if ($escrow->status !== EscrowStatus::Holding && $escrow->status !== EscrowStatus::Frozen) {
            $this->dispatch('swal:error', [
                'message' => 'Only escrows with Holding or Frozen status can be extended.',
            ]);

            return;
        }

        // Check if already at max extension
        $maxAllowedDate = $escrow->created_at->addDays(self::MAX_EXTEND_DAYS_FROM_CREATED);
        if ($escrow->release_date->greaterThanOrEqualTo($maxAllowedDate)) {
            $this->dispatch('swal:error', [
                'message' => 'This escrow has reached the maximum extension limit.',
            ]);

            return;
        }

        $this->extendEscrowId = $id;
        $this->extendDays = null;
        $this->showExtendModal = true;
    }

    public function performExtendHolding(): void
    {
        try {
            $this->validate();

            DB::transaction(function () {
                $escrow = Escrow::lockForUpdate()->findOrFail($this->extendEscrowId);

                // Validate status
                if ($escrow->status !== EscrowStatus::Holding && $escrow->status !== EscrowStatus::Frozen) {
                    throw new Exception('Escrow status is no longer valid for extension.');
                }

                // Calculate new release date
                $newReleaseDate = $escrow->release_date->addDays($this->extendDays);
                $maxAllowedDate = $escrow->created_at->addDays(self::MAX_EXTEND_DAYS_FROM_CREATED);

                // Validate max extension constraint
                if ($newReleaseDate->greaterThan($maxAllowedDate)) {
                    throw new Exception('Extension exceeds maximum allowed limit. Max extend to: '.$maxAllowedDate->format('d/m/Y'));
                }

                $oldValues = [
                    'release_date' => $escrow->release_date->toDateTimeString(),
                    'updated_at' => $escrow->updated_at->toDateTimeString(),
                ];

                // Update release_date
                $escrow->release_date = $newReleaseDate;
                $escrow->save();

                $newValues = [
                    'release_date' => $escrow->release_date->toDateTimeString(),
                    'updated_at' => $escrow->updated_at->toDateTimeString(),
                ];

                // Create audit log
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'auditable_type' => Escrow::class,
                    'auditable_id' => $escrow->id,
                    'event' => AuditEvent::EscrowExtended,
                    'old_values' => array_merge($oldValues, ['days_extended' => $this->extendDays]),
                    'new_values' => $newValues,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                ]);
            });

            $this->dispatch('swal:success', [
                'message' => 'Escrow holding time extended successfully by '.$this->extendDays.' day(s).',
            ]);

            $this->showExtendModal = false;
            $this->extendEscrowId = null;
            $this->extendDays = null;
        } catch (Exception $e) {
            $this->dispatch('swal:error', [
                'message' => 'Failed to extend escrow: '.$e->getMessage(),
            ]);
        }
    }
}
