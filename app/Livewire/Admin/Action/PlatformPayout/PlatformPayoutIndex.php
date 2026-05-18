<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\PlatformPayout;

use App\Enums\InternalWalletEntryType;
use App\Enums\PlatformPayoutStatus;
use App\Enums\UserRole;
use App\Models\InternalWalletEntry;
use App\Models\PlatformPayout;
use App\Models\User;
use App\Services\Shop\PlatformPayoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Title('Platform Payouts')]
class PlatformPayoutIndex extends Component
{
    public bool $showViewModal = false;

    public ?array $viewData = null;

    public function render(PlatformPayoutService $platformPayoutService): View
    {
        return view('pages.admin.platform-payout.index', [
            'metrics' => $this->metrics(),
            'preview' => $platformPayoutService->preview(),
            'payouts' => $this->payouts(),
        ])->layout('components.layouts.dashboard');
    }

    public function viewPayout(int $platformPayoutId): void
    {
        $platformPayout = $this->payouts()->firstWhere('id', $platformPayoutId)
            ?? $this->findPlatformPayout($platformPayoutId);

        $requestedEntry = $this->findLedgerEntry($platformPayout->id, InternalWalletEntryType::PlatformProfitPayoutRequested);
        $completedEntry = $this->findLedgerEntry($platformPayout->id, InternalWalletEntryType::PlatformProfitPayoutCompleted);
        $failedEntry = $this->findLedgerEntry($platformPayout->id, InternalWalletEntryType::PlatformProfitPayoutFailed);

        $platformFeeAmount = (float) $platformPayout->items
            ->where('profit_type', 'platform_fee')
            ->sum('amount');

        $platformOwnedAmount = (float) $platformPayout->items
            ->where('profit_type', 'platform_owned_sale')
            ->sum('amount');

        $this->viewData = [
            'id'                   => $platformPayout->id,
            'payout_code'          => $platformPayout->payout_code,
            'period'               => sprintf('%s - %s', $platformPayout->period_start?->format('d/m/Y'), $platformPayout->period_end?->format('d/m/Y')),
            'settlement_cutoff_at' => $platformPayout->settlement_cutoff_at?->format('d/m/Y H:i:s') ?? '-',
            'amount'               => number_format((float) $platformPayout->amount, 2).' VND',
            'status_label'         => sprintf(
                '<span class="%s text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">%s</span>',
                $this->statusClass($platformPayout->status),
                $platformPayout->status->label(),
            ),
            'bank_name'             => $platformPayout->bank_name,
            'bank_code'             => $platformPayout->bank_code,
            'account_name'          => $platformPayout->bank_account_name,
            'account_number'        => $this->maskAccountNumber((string) $platformPayout->bank_account_number),
            'items_count'           => $platformPayout->items->count(),
            'platform_fee_amount'   => number_format($platformFeeAmount, 2).' VND',
            'platform_owned_amount' => number_format($platformOwnedAmount, 2).' VND',
            'requested_entry'       => $requestedEntry?->id,
            'completed_entry'       => $completedEntry?->id,
            'failed_entry'          => $failedEntry?->id,
            'processed_at'          => $platformPayout->processed_at?->format('d/m/Y H:i:s') ?? '-',
            'created_at'            => $platformPayout->created_at?->format('d/m/Y H:i:s') ?? '-',
            'metadata'              => $platformPayout->metadata ?? [],
            'items'                 => $platformPayout->items->map(function ($item): array {
                return [
                    'id'              => $item->id,
                    'order_code'      => $item->orderItem?->order?->order_code ?? '-',
                    'order_item_code' => $item->orderItem?->order_item_code ?? '-',
                    'product_name'    => $item->orderItem?->product_name_snapshot ?? '-',
                    'profit_type'     => $item->profit_type === 'platform_fee'
                        ? __('admin.platform_payouts.profit_type_platform_fee')
                        : __('admin.platform_payouts.profit_type_platform_owned_sale'),
                    'amount'       => number_format((float) $item->amount, 2).' VND',
                    'completed_at' => $item->orderItem?->completed_at?->format('d/m/Y H:i:s') ?? '-',
                ];
            })->all(),
        ];

        $this->showViewModal = true;
    }

    public function process(int $platformPayoutId, PlatformPayoutService $platformPayoutService): void
    {
        $this->runAction($platformPayoutId, $platformPayoutService, 'process', __('admin.messages.platform_payout_processed'), __('admin.messages.platform_payout_process_failed', ['error' => ':error']));
    }

    public function retry(int $platformPayoutId, PlatformPayoutService $platformPayoutService): void
    {
        $this->runAction($platformPayoutId, $platformPayoutService, 'process', __('admin.messages.platform_payout_retried'), __('admin.messages.platform_payout_retry_failed', ['error' => ':error']));
    }

    public function cancel(int $platformPayoutId, PlatformPayoutService $platformPayoutService): void
    {
        $this->runAction($platformPayoutId, $platformPayoutService, 'cancel', __('admin.messages.platform_payout_cancelled'), __('admin.messages.platform_payout_cancel_failed', ['error' => ':error']));
    }

    /**
     * @return Collection<int, PlatformPayout>
     */
    protected function payouts(): Collection
    {
        return PlatformPayout::query()
            ->withCount('items')
            ->with(['items.orderItem.order'])
            ->orderByRaw('CASE WHEN status IN (?, ?) THEN 0 ELSE 1 END', [
                PlatformPayoutStatus::Pending->value,
                PlatformPayoutStatus::Failed->value,
            ])
            ->orderByDesc('period_end')
            ->limit(100)
            ->get();
    }

    /**
     * @return array<string, float|int>
     */
    protected function metrics(): array
    {
        return [
            'completedAmount' => (float) PlatformPayout::query()
                ->where('status', PlatformPayoutStatus::Completed->value)
                ->sum('amount'),
            'pendingAmount' => (float) PlatformPayout::query()
                ->where('status', PlatformPayoutStatus::Pending->value)
                ->sum('amount'),
            'failedAmount' => (float) PlatformPayout::query()
                ->where('status', PlatformPayoutStatus::Failed->value)
                ->sum('amount'),
            'lastCompletedAt' => PlatformPayout::query()
                ->where('status', PlatformPayoutStatus::Completed->value)
                ->max('processed_at'),
            'batchCount' => PlatformPayout::query()->count(),
        ];
    }

    protected function adminUser(): User
    {
        $user = auth('admin')->user();

        abort_unless($user instanceof User, 403);
        abort_unless(in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin], true), 403);

        return $user;
    }

    protected function findPlatformPayout(int $platformPayoutId): PlatformPayout
    {
        return PlatformPayout::query()
            ->withCount('items')
            ->with(['items.orderItem.order'])
            ->findOrFail($platformPayoutId);
    }

    protected function statusClass(PlatformPayoutStatus $status): string
    {
        return match ($status) {
            PlatformPayoutStatus::Completed  => 'bg-emerald-500/10 text-emerald-600',
            PlatformPayoutStatus::Pending    => 'bg-amber-500/10 text-amber-600',
            PlatformPayoutStatus::Processing => 'bg-sky-500/10 text-sky-600',
            PlatformPayoutStatus::Failed     => 'bg-rose-500/10 text-rose-600',
            default                          => 'bg-slate-500/10 text-slate-600',
        };
    }

    protected function maskAccountNumber(string $accountNumber): string
    {
        $length = strlen($accountNumber);

        if ($length <= 4) {
            return $accountNumber;
        }

        return str_repeat('*', $length - 4).substr($accountNumber, -4);
    }

    protected function findLedgerEntry(int $platformPayoutId, InternalWalletEntryType $type): ?InternalWalletEntry
    {
        return InternalWalletEntry::query()
            ->where('source_type', PlatformPayout::class)
            ->where('source_id', $platformPayoutId)
            ->where('type', $type)
            ->first();
    }

    protected function runAction(int $platformPayoutId, PlatformPayoutService $platformPayoutService, string $method, string $successMessage, string $errorTemplate): void
    {
        $this->adminUser();

        try {
            $platformPayoutService->{$method}($this->findPlatformPayout($platformPayoutId));

            $this->dispatch('swal:success', [
                'message' => $successMessage,
            ]);
        } catch (Throwable $throwable) {
            if ($throwable instanceof ValidationException) {
                $message = collect($throwable->errors())->flatten()->first() ?? $throwable->getMessage();

                $this->dispatch('swal:error', [
                    'message' => $message,
                ]);

                return;
            }

            report($throwable);

            $this->dispatch('swal:error', [
                'message' => str_replace(':error', $throwable->getMessage(), $errorTemplate),
            ]);
        }
    }
}
