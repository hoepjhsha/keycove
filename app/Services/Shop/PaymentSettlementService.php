<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Wallet;
use App\Services\InternalWalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentSettlementService
{
    public function __construct(private InternalWalletService $internalWalletService) {}

    public function settlePaidOrder(Order $order, array $paymentData = []): void
    {
        DB::transaction(function () use ($order, $paymentData): void {
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->with(['items.keys', 'items.escrow', 'paymentTransactions'])
                ->firstOrFail();

            if ($order->payment_status !== PaymentStatus::Pending) {
                return;
            }

            $order->forceFill([
                'payment_status' => PaymentStatus::Completed,
            ])->save();

            PaymentTransaction::query()
                ->where('order_id', $order->id)
                ->latest('id')
                ->limit(1)
                ->update([
                    'status'           => PaymentStatus::Completed,
                    'paid_at'          => now(),
                    'response_payload' => $paymentData,
                ]);

            $paymentTransaction = PaymentTransaction::query()
                ->where('order_id', $order->id)
                ->latest('id')
                ->first();

            if ($paymentTransaction !== null) {
                $this->internalWalletService->paymentReceived($order, $paymentTransaction, $paymentTransaction->paid_at ?? now());
            }

            foreach ($order->items as $item) {
                $item->forceFill([
                    'status'       => OrderStatus::Delivered,
                    'delivered_at' => now(),
                    'completed_at' => null,
                ])->save();

                $item->keys()->update([
                    'status' => ProductKeyStatus::Sold,
                ]);

                $item->listing()->decrement('stock_count', (int) $item->quantity);

                if ($item->escrow !== null) {
                    $item->escrow->forceFill([
                        'status' => EscrowStatus::Holding,
                    ])->save();

                    $this->internalWalletService->escrowHeld($item->escrow, $item->escrow->created_at ?? now(), [
                        'order_item_id' => $item->id,
                    ]);
                }

                if ($item->seller_id !== null) {
                    $wallet = Wallet::firstOrCreate(
                        ['seller_id' => $item->seller_id],
                        ['type' => WalletType::Seller, 'code' => Str::upper(Str::random(12)), 'balance' => 0, 'holding' => 0]
                    );

                    $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

                    $wallet->forceFill([
                        'holding' => round((float) $wallet->holding + (float) $item->seller_amount, 2),
                    ])->save();

                    $wallet->transactions()->create([
                        'order_id'     => $order->id,
                        'source_type'  => $item->escrow ? Escrow::class : null,
                        'source_id'    => $item->escrow?->id,
                        'type'         => TransactionType::PaymentReceived,
                        'balance_type' => TransactionBalanceType::Holding,
                        'payment_info' => $paymentData,
                        'amount'       => (float) $item->seller_amount,
                        'status'       => TransactionStatus::Completed,
                        'metadata'     => ['order_item_id' => $item->id],
                    ]);
                } else {
                    $internalWallet = Wallet::query()
                        ->whereKey($this->internalWalletService->wallet()->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $internalWallet->transactions()->create([
                        'order_id'     => $order->id,
                        'source_type'  => OrderItem::class,
                        'source_id'    => $item->id,
                        'type'         => TransactionType::PaymentReceived,
                        'balance_type' => TransactionBalanceType::Available,
                        'payment_info' => $paymentData,
                        'amount'       => (float) $item->seller_amount,
                        'status'       => TransactionStatus::Completed,
                        'metadata'     => ['order_item_id' => $item->id],
                    ]);
                }
            }
        });
    }
}
