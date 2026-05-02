<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\TransactionStatus;
use App\Managers\PaymentManager;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class PendingOrderService
{
    public function paymentUrl(Order $order, PaymentManager $paymentManager): string
    {
        abort_unless($order->payment_status === PaymentStatus::Pending, 422, 'This order is no longer waiting for payment.');

        return $paymentManager->driver('vnpay')->createPayment([
            'txn_ref'    => $order->order_code,
            'amount'     => (float) $order->total_price,
            'order_info' => 'Thanh toán cho '.$order->order_code,
            'order_type' => 'other',
            'locale'     => 'vn',
        ]);
    }

    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->with(['items.keys', 'items.escrow', 'paymentTransactions', 'transactions'])
                ->firstOrFail();

            if ($order->payment_status !== PaymentStatus::Pending) {
                return;
            }

            foreach ($order->items as $item) {
                $item->forceFill([
                    'status' => OrderStatus::Cancelled,
                ])->save();

                $item->keys()
                    ->where('status', ProductKeyStatus::Reserved->value)
                    ->update([
                        'status'        => ProductKeyStatus::Available,
                        'order_item_id' => null,
                    ]);

                $item->escrow()?->delete();
            }

            $order->paymentTransactions()
                ->where('status', PaymentStatus::Pending->value)
                ->update([
                    'status' => PaymentStatus::Cancelled,
                ]);

            $order->transactions()
                ->where('status', TransactionStatus::Pending->value)
                ->update([
                    'status' => TransactionStatus::Cancelled,
                ]);

            $order->forceFill([
                'payment_status' => PaymentStatus::Cancelled,
            ])->save();
        }, 5);
    }
}
