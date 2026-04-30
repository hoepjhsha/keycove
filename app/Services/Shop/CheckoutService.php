<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Models\Cart;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\SystemConfig;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CheckoutService
{
    /**
     * @param  list<int>  $selectedCartItemIds
     */
    public function createOrderFromCart(
        Cart $cart,
        PaymentMethod $paymentMethod = PaymentMethod::VNPay,
        array $selectedCartItemIds = []
    ): Order {
        $selectedCartItemIds = array_values(array_unique(array_map('intval', $selectedCartItemIds)));
        $hasOrderItemCodeColumn = Schema::hasColumn('order_items', 'order_item_code');

        $cart->loadMissing([
            'user.seller',
            'items.listing.variant.product',
            'items.listing.variant.region',
            'items.listing.variant.platform',
            'items.listing.variant.operatingSystem',
        ]);

        return DB::transaction(function () use ($cart, $paymentMethod, $hasOrderItemCodeColumn, $selectedCartItemIds): Order {
            $items = $cart->items
                ->filter(function ($item) use ($selectedCartItemIds): bool {
                    if ($item->listing === null) {
                        return false;
                    }

                    if ($selectedCartItemIds === []) {
                        return true;
                    }

                    return in_array((int) $item->id, $selectedCartItemIds, true);
                })
                ->values();

            abort_if($items->isEmpty(), 422, 'Your cart is empty.');

            $order = Order::create([
                'buyer_id'       => (int) $cart->user_id,
                'order_code'     => $this->generateOrderCode(),
                'total_price'    => 0,
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::Pending,
            ]);

            $buyerSellerId = $cart->user?->seller?->id;

            $totalPrice = 0.0;

            foreach ($items as $cartItem) {
                $listing = $cartItem->listing;

                if ($listing === null) {
                    continue;
                }

                if ($buyerSellerId !== null && $buyerSellerId === $listing->seller_id) {
                    abort(422, 'You cannot purchase your own listing.');
                }

                $quantity = min(max(1, (int) $cartItem->quantity), max(0, (int) $listing->stock_count));
                abort_if($quantity < 1, 422, 'One of the cart items is out of stock.');

                $keys = ProductKey::query()
                    ->where('listing_id', $listing->id)
                    ->where('status', ProductKeyStatus::Available->value)
                    ->lockForUpdate()
                    ->limit($quantity)
                    ->get();

                abort_if($keys->count() < $quantity, 422, 'One of the cart items does not have enough available keys.');

                $subtotal = (float) $listing->price * $quantity;
                $commissionRate = $this->commissionRate();
                $platformFee = round($subtotal * ($commissionRate / 100), 2);
                $sellerAmount = round($subtotal - $platformFee, 2);

                $orderItemAttributes = [
                    'order_id'              => $order->id,
                    'listing_id'            => $listing->id,
                    'seller_id'             => $listing->seller_id,
                    'product_name_snapshot' => $listing->display_name ?: ($listing->variant?->product?->name ?? 'Unknown item'),
                    'variant_snapshot'      => [
                        'variant_id'       => $listing->variant_id,
                        'product_id'       => $listing->variant?->product_id,
                        'edition'          => $listing->variant?->edition,
                        'region'           => $listing->variant?->region?->slug,
                        'platform'         => $listing->variant?->platform?->slug,
                        'operating_system' => $listing->variant?->operatingSystem?->slug,
                    ],
                    'quantity'      => $quantity,
                    'unit_price'    => $listing->price,
                    'subtotal'      => $subtotal,
                    'platform_fee'  => $platformFee,
                    'seller_amount' => $sellerAmount,
                    'status'        => OrderStatus::PendingPayment,
                ];

                if ($hasOrderItemCodeColumn) {
                    $orderItemAttributes['order_item_code'] = $this->generateOrderItemCode();
                }

                $orderItem = OrderItem::create($orderItemAttributes);

                foreach ($keys as $key) {
                    $key->forceFill([
                        'status'        => ProductKeyStatus::Reserved,
                        'order_item_id' => $orderItem->id,
                    ])->save();
                }

                $wallet = $this->resolveWallet($listing);

                Transaction::create([
                    'wallet_id'    => $wallet->id,
                    'order_id'     => $order->id,
                    'source_type'  => $listing->seller_id !== null ? Escrow::class : OrderItem::class,
                    'source_id'    => $orderItem->id,
                    'type'         => TransactionType::PaymentReceived,
                    'balance_type' => $listing->seller_id !== null
                        ? TransactionBalanceType::Holding
                        : TransactionBalanceType::Available,
                    'payment_info' => null,
                    'amount'       => $sellerAmount,
                    'status'       => TransactionStatus::Pending,
                    'metadata'     => [
                        'order_item_id' => $orderItem->id,
                    ],
                ]);

                if ($listing->seller_id !== null) {
                    Escrow::create([
                        'order_item_id' => $orderItem->id,
                        'seller_id'     => $listing->seller_id,
                        'amount'        => $sellerAmount,
                        'release_date'  => now()->addDays(3),
                        'status'        => EscrowStatus::Holding,
                    ]);
                }

                $totalPrice += $subtotal;
            }

            $order->forceFill([
                'total_price' => $totalPrice,
            ])->save();

            PaymentTransaction::create([
                'order_id'               => $order->id,
                'gateway'                => $paymentMethod,
                'gateway_transaction_id' => $order->order_code,
                'amount'                 => $totalPrice,
                'status'                 => PaymentStatus::Pending,
                'request_payload'        => ['cart_id' => $cart->id],
            ]);

            $cart->items()
                ->whereKey($items->pluck('id')->all())
                ->delete();

            return $order;
        }, 5);
    }

    protected function generateOrderCode(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
    }

    protected function generateOrderItemCode(): string
    {
        return 'OI-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }

    protected function commissionRate(): float
    {
        $configuredValue = SystemConfig::query()
            ->where('key', 'commission_rate')
            ->value('value');

        return max(0.0, (float) ($configuredValue ?: 10));
    }

    protected function resolveWallet(ProductListing $listing): Wallet
    {
        if ($listing->seller_id !== null) {
            return Wallet::firstOrCreate(
                ['seller_id' => $listing->seller_id],
                ['type' => WalletType::Seller, 'code' => Str::upper(Str::random(12)), 'balance' => 0, 'holding' => 0]
            );
        }

        return Wallet::firstOrCreate(
            ['seller_id' => null, 'type' => WalletType::Internal],
            ['code' => Str::upper(Str::random(12)), 'balance' => 0, 'holding' => 0]
        );
    }
}
