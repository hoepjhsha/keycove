<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PlatformPayoutStatus;
use App\Enums\TransactionStatus;
use App\Managers\PaymentManager;
use App\Models\OrderItem;
use App\Models\PlatformPayout;
use App\Models\SystemConfig;
use App\Services\InternalWalletService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlatformPayoutService
{
    public function __construct(
        private PaymentManager $payments,
        private InternalWalletService $internalWalletService,
    ) {}

    /**
     * @return array{period_start: CarbonImmutable, period_end: CarbonImmutable, settlement_cutoff_at: CarbonImmutable}
     */
    public function payoutWindow(?CarbonImmutable $runAt = null): array
    {
        $runAt ??= CarbonImmutable::now();

        $settlementCutoffAt = $runAt->subDays($this->settlementDays())->endOfDay();
        $periodEnd = $settlementCutoffAt;
        $periodStart = $settlementCutoffAt->subDays(6)->startOfDay();

        return [
            'period_start'         => $periodStart,
            'period_end'           => $periodEnd,
            'settlement_cutoff_at' => $settlementCutoffAt,
        ];
    }

    /**
     * @return array{period_start: CarbonImmutable, period_end: CarbonImmutable, settlement_cutoff_at: CarbonImmutable, eligible_count: int, eligible_amount: float}
     */
    public function preview(?CarbonImmutable $runAt = null): array
    {
        $window = $this->payoutWindow($runAt);
        $eligibleItems = $this->eligibleOrderItems($window['period_start'], $window['period_end'])->get();

        return [
            ...$window,
            'eligible_count'  => $eligibleItems->count(),
            'eligible_amount' => round($eligibleItems->sum(fn (OrderItem $orderItem): float => $this->profitAmount($orderItem)), 2),
        ];
    }

    public function runWeekly(?CarbonImmutable $runAt = null, bool $process = true): ?PlatformPayout
    {
        if (! $this->payoutEnabled()) {
            return null;
        }

        $platformPayout = $this->findOrCreateWeeklyPayout($runAt);

        if ($platformPayout === null || ! $process || ! $this->autoProcessEnabled()) {
            return $platformPayout;
        }

        if (in_array($platformPayout->status, [PlatformPayoutStatus::Completed, PlatformPayoutStatus::Cancelled], true)) {
            return $platformPayout;
        }

        return $this->process($platformPayout);
    }

    public function findOrCreateWeeklyPayout(?CarbonImmutable $runAt = null): ?PlatformPayout
    {
        $window = $this->payoutWindow($runAt);
        $idempotencyKey = sprintf(
            'platform-payout:%s:%s:%d',
            $window['period_start']->toDateString(),
            $window['period_end']->toDateString(),
            $this->settlementDays(),
        );

        $existingPayout = PlatformPayout::query()
            ->with('items')
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingPayout !== null) {
            return $existingPayout;
        }

        $eligibleItems = $this->eligibleOrderItems($window['period_start'], $window['period_end'])->get();

        if ($eligibleItems->isEmpty()) {
            return null;
        }

        $amount = round($eligibleItems->sum(fn (OrderItem $orderItem): float => $this->profitAmount($orderItem)), 2);

        if ($amount <= 0) {
            return null;
        }

        $bankDetails = $this->bankDetails();

        $platformPayout = DB::transaction(function () use ($window, $idempotencyKey, $amount, $eligibleItems, $bankDetails): PlatformPayout {
            $platformPayout = PlatformPayout::query()->create([
                'payout_code'          => 'PPO-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'period_start'         => $window['period_start']->toDateString(),
                'period_end'           => $window['period_end']->toDateString(),
                'settlement_cutoff_at' => $window['settlement_cutoff_at'],
                'amount'               => $amount,
                'status'               => PlatformPayoutStatus::Pending,
                'bank_name'            => $bankDetails['bank_name'],
                'bank_code'            => $bankDetails['bank_code'],
                'bank_account_number'  => $bankDetails['bank_account_number'],
                'bank_account_name'    => $bankDetails['bank_account_name'],
                'idempotency_key'      => $idempotencyKey,
                'metadata'             => [
                    'eligible_order_item_count' => $eligibleItems->count(),
                    'settlement_days'           => $this->settlementDays(),
                ],
            ]);

            foreach ($eligibleItems as $orderItem) {
                $platformPayout->items()->create([
                    'order_item_id' => $orderItem->id,
                    'amount'        => $this->profitAmount($orderItem),
                    'profit_type'   => $orderItem->seller_id === null ? 'platform_owned_sale' : 'platform_fee',
                    'metadata'      => [
                        'seller_id'    => $orderItem->seller_id,
                        'order_id'     => $orderItem->order_id,
                        'completed_at' => $orderItem->completed_at?->toDateTimeString(),
                    ],
                ]);
            }

            return $platformPayout;
        }, 5);

        $this->internalWalletService->platformProfitPayoutRequested($platformPayout);

        return $platformPayout->fresh(['items']);
    }

    public function process(PlatformPayout $platformPayout): PlatformPayout
    {
        $platformPayout = PlatformPayout::query()->with('items')->findOrFail($platformPayout->id);

        if ($platformPayout->status === PlatformPayoutStatus::Completed || $platformPayout->status === PlatformPayoutStatus::Cancelled) {
            throw ValidationException::withMessages([
                'platform_payout' => 'This platform payout can no longer be processed.',
            ]);
        }

        if ($platformPayout->status === PlatformPayoutStatus::Processing) {
            throw ValidationException::withMessages([
                'platform_payout' => 'This platform payout is already processing.',
            ]);
        }

        $this->internalWalletService->platformProfitPayoutRequested($platformPayout);

        $bankDetails = $this->bankDetails();

        if ($bankDetails['bank_name'] === '' || $bankDetails['bank_code'] === '' || $bankDetails['bank_account_number'] === '' || $bankDetails['bank_account_name'] === '') {
            return $this->markFailed($platformPayout, 'Platform payout bank configuration is incomplete.');
        }

        $internalWallet = $this->internalWalletService->wallet()->fresh();

        if ((float) ($internalWallet?->balance ?? 0) < (float) $platformPayout->amount) {
            return $this->markFailed($platformPayout, 'Insufficient internal wallet balance for platform payout.');
        }

        PlatformPayout::query()->whereKey($platformPayout->id)->update([
            'status' => PlatformPayoutStatus::Processing,
        ]);

        $response = $this->payments->driver('vnpay')->withdraw([
            'amount'         => (float) $platformPayout->amount,
            'bank_code'      => $platformPayout->bank_code,
            'account_number' => $platformPayout->bank_account_number,
            'account_name'   => $platformPayout->bank_account_name,
            'order_info'     => 'Payout lợi nhuận nền tảng '.$platformPayout->payout_code,
            'txn_ref'        => 'PPO-'.$platformPayout->id,
            'request_id'     => 'PPOREQ-'.$platformPayout->id,
            'create_by'      => 'system',
            'ip_address'     => '127.0.0.1',
        ]);

        if ($response['success'] ?? false) {
            DB::transaction(function () use ($platformPayout, $response): void {
                $lockedPayout = PlatformPayout::query()->whereKey($platformPayout->id)->lockForUpdate()->firstOrFail();

                $lockedPayout->forceFill([
                    'status'       => PlatformPayoutStatus::Completed,
                    'processed_at' => now(),
                    'metadata'     => array_merge($lockedPayout->metadata ?? [], [
                        'gateway_message'  => $response['message'] ?? null,
                        'gateway_response' => $response['payload'] ?? null,
                    ]),
                ])->save();

                $this->internalWalletService->platformProfitPayoutCompleted($lockedPayout, $lockedPayout->processed_at ?? now(), [
                    'gateway_message'  => $response['message'] ?? null,
                    'gateway_response' => $response['payload'] ?? null,
                ]);
            }, 5);

            return $platformPayout->fresh(['items']);
        }

        return $this->markFailed($platformPayout, (string) ($response['message'] ?? 'Platform payout was rejected by the payment gateway.'), $response['payload'] ?? null);
    }

    public function cancel(PlatformPayout $platformPayout): PlatformPayout
    {
        return DB::transaction(function () use ($platformPayout): PlatformPayout {
            $lockedPayout = PlatformPayout::query()->whereKey($platformPayout->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayout->status !== PlatformPayoutStatus::Pending) {
                throw ValidationException::withMessages([
                    'platform_payout' => 'Only pending platform payouts can be cancelled.',
                ]);
            }

            $lockedPayout->forceFill([
                'status'       => PlatformPayoutStatus::Cancelled,
                'processed_at' => now(),
                'metadata'     => array_merge($lockedPayout->metadata ?? [], [
                    'cancelled_by' => 'admin',
                ]),
            ])->save();

            $this->internalWalletService->platformProfitPayoutRequested($lockedPayout, TransactionStatus::Cancelled);

            return $lockedPayout;
        }, 5)->fresh(['items']);
    }

    protected function markFailed(PlatformPayout $platformPayout, string $message, mixed $payload = null): PlatformPayout
    {
        DB::transaction(function () use ($platformPayout, $message, $payload): void {
            $lockedPayout = PlatformPayout::query()->whereKey($platformPayout->id)->lockForUpdate()->firstOrFail();

            $lockedPayout->forceFill([
                'status'       => PlatformPayoutStatus::Failed,
                'processed_at' => now(),
                'metadata'     => array_merge($lockedPayout->metadata ?? [], array_filter([
                    'gateway_message'  => $message,
                    'gateway_response' => is_array($payload) ? $payload : null,
                ], fn (mixed $value): bool => $value !== null)),
            ])->save();

            $this->internalWalletService->platformProfitPayoutFailed($lockedPayout, $lockedPayout->processed_at ?? now(), [
                'gateway_message'  => $message,
                'gateway_response' => is_array($payload) ? $payload : null,
            ]);
        }, 5);

        return $platformPayout->fresh(['items']);
    }

    protected function eligibleOrderItems(CarbonImmutable $periodStart, CarbonImmutable $periodEnd)
    {
        return OrderItem::query()
            ->with('order')
            ->where('status', OrderStatus::Completed)
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$periodStart, $periodEnd])
            ->whereDoesntHave('platformPayoutItem')
            ->whereHas('order', function ($query): void {
                $query->where('payment_status', PaymentStatus::Completed->value);
            })
            ->where(function ($query): void {
                $query->where('platform_fee', '>', 0)
                    ->orWhere(function ($subQuery): void {
                        $subQuery->whereNull('seller_id')
                            ->where('seller_amount', '>', 0);
                    });
            })
            ->orderBy('completed_at')
            ->orderBy('id');
    }

    protected function profitAmount(OrderItem $orderItem): float
    {
        if ($orderItem->seller_id === null) {
            return round((float) $orderItem->seller_amount, 2);
        }

        return round((float) $orderItem->platform_fee, 2);
    }

    /**
     * @return array{bank_name: string, bank_code: string, bank_account_number: string, bank_account_name: string}
     */
    protected function bankDetails(): array
    {
        return [
            'bank_name'           => $this->stringConfig('platform_payout_bank_name'),
            'bank_code'           => $this->stringConfig('platform_payout_bank_code'),
            'bank_account_number' => $this->stringConfig('platform_payout_bank_account_number'),
            'bank_account_name'   => $this->stringConfig('platform_payout_bank_account_name'),
        ];
    }

    protected function payoutEnabled(): bool
    {
        return $this->booleanConfig('platform_payout_enabled', true);
    }

    protected function autoProcessEnabled(): bool
    {
        return $this->booleanConfig('platform_payout_auto_process', true);
    }

    protected function settlementDays(): int
    {
        return max(1, $this->integerConfig('platform_payout_settlement_days', 7));
    }

    protected function stringConfig(string $key, string $default = ''): string
    {
        return (string) (SystemConfig::query()->where('key', $key)->value('value') ?? $default);
    }

    protected function integerConfig(string $key, int $default): int
    {
        return (int) (SystemConfig::query()->where('key', $key)->value('value') ?? $default);
    }

    protected function booleanConfig(string $key, bool $default): bool
    {
        $value = SystemConfig::query()->where('key', $key)->value('value');

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
