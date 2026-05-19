<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Models\Escrow;
use App\Models\OrderItem;
use App\Models\Wallet;
use App\Services\InternalWalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderItemCompletionService
{
    public function __construct(private InternalWalletService $internalWalletService) {}

    public function complete(int $orderItemId, string $source): bool
    {
        return DB::transaction(function () use ($orderItemId, $source): bool {
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
                if (in_array($escrow->status, [EscrowStatus::Frozen, EscrowStatus::Refunded], true)) {
                    return false;
                }

                if ($escrow->status === EscrowStatus::Holding) {
                    $this->releaseEscrow($orderItem, $escrow, $source);
                }
            }

            $orderItem->forceFill([
                'status'       => OrderStatus::Completed,
                'completed_at' => now(),
            ])->save();

            return true;
        }, 5);
    }

    protected function releaseEscrow(OrderItem $orderItem, Escrow $escrow, string $source): void
    {
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
                'source'    => $source,
            ],
            'amount'   => $amount,
            'status'   => TransactionStatus::Completed,
            'metadata' => [
                'escrow_id'     => $escrow->id,
                'order_item_id' => $orderItem->id,
            ],
        ]);

        $this->internalWalletService->escrowReleased($escrow, now(), [
            'escrow_id'     => $escrow->id,
            'order_item_id' => $orderItem->id,
            'source'        => $source,
        ]);
    }
}
