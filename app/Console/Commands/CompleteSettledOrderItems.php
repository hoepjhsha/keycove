<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Models\Escrow;
use App\Models\OrderItem;
use App\Models\SystemConfig;
use App\Models\Wallet;
use App\Services\InternalWalletService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Signature('app:complete-settled-order-items {--days= : Days to wait before auto-completing delivered order items}')]
#[Description('Auto-complete delivered order items after the buyer response window closes')]
class CompleteSettledOrderItems extends Command
{
    public function handle(InternalWalletService $internalWalletService): int
    {
        $completionDays = max(1, (int) ($this->option('days') ?: $this->configuredDays()));
        $threshold = now()->subDays($completionDays);
        $completedCount = 0;

        OrderItem::query()
            ->where('status', OrderStatus::Delivered)
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', $threshold)
            ->whereDoesntHave('complaint')
            ->orderBy('id')
            ->chunkById(100, function ($orderItems) use ($internalWalletService, &$completedCount): void {
                foreach ($orderItems as $orderItem) {
                    if ($this->completeOrderItem($orderItem->id, $internalWalletService)) {
                        $completedCount++;
                    }
                }
            });

        $this->info(sprintf('Completed %d settled order item(s).', $completedCount));

        return self::SUCCESS;
    }

    protected function completeOrderItem(int $orderItemId, InternalWalletService $internalWalletService): bool
    {
        return DB::transaction(function () use ($orderItemId, $internalWalletService): bool {
            $orderItem = OrderItem::query()
                ->whereKey($orderItemId)
                ->lockForUpdate()
                ->with(['escrow', 'complaint'])
                ->firstOrFail();

            if ($orderItem->status !== OrderStatus::Delivered || $orderItem->complaint !== null) {
                return false;
            }

            $escrow = $orderItem->escrow;

            if ($escrow !== null) {
                if ($escrow->status === EscrowStatus::Frozen || $escrow->status === EscrowStatus::Refunded) {
                    return false;
                }

                if ($escrow->status === EscrowStatus::Holding) {
                    $wallet = Wallet::firstOrCreate(
                        ['seller_id' => $escrow->seller_id],
                        ['type' => WalletType::Seller, 'code' => Str::upper(Str::random(12)), 'balance' => 0, 'holding' => 0]
                    );

                    $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
                    $amount = (float) $escrow->amount;

                    $escrow->forceFill([
                        'status' => EscrowStatus::Released,
                    ])->save();

                    $wallet->forceFill([
                        'holding' => round((float) $wallet->holding - $amount, 2),
                        'balance' => round((float) $wallet->balance + $amount, 2),
                    ])->save();

                    $wallet->transactions()->create([
                        'order_id'     => $orderItem->order_id,
                        'source_type'  => Escrow::class,
                        'source_id'    => $escrow->id,
                        'type'         => TransactionType::EscrowRelease,
                        'balance_type' => TransactionBalanceType::Available,
                        'payment_info' => [
                            'escrow_id' => $escrow->id,
                            'source'    => 'auto_complete',
                        ],
                        'amount'   => $amount,
                        'status'   => TransactionStatus::Completed,
                        'metadata' => [
                            'escrow_id'     => $escrow->id,
                            'order_item_id' => $orderItem->id,
                        ],
                    ]);

                    $internalWalletService->escrowReleased($escrow, now(), [
                        'escrow_id'     => $escrow->id,
                        'order_item_id' => $orderItem->id,
                        'source'        => 'auto_complete',
                    ]);
                }
            }

            $orderItem->forceFill([
                'status'       => OrderStatus::Completed,
                'completed_at' => now(),
            ])->save();

            return true;
        }, 5);
    }

    protected function configuredDays(): int
    {
        return (int) (SystemConfig::query()->where('key', 'order_auto_complete_days')->value('value') ?? 7);
    }
}
