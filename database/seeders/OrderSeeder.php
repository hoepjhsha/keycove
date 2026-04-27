<?php

namespace Database\Seeders;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Enums\WithdrawStatus;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdraw;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedOrders();
    }

    protected function seedOrders(): void
    {
        $buyers = User::where('role', UserRole::User)->get();
        $activeListings = ProductListing::with(['variant.product', 'variant.region', 'variant.platform', 'variant.operatingSystem', 'seller'])
            ->where('status', ProductListingStatus::Active)
            ->get();

        if ($buyers->isEmpty() || $activeListings->isEmpty()) {
            return;
        }

        $orderConfigs = [
            ['status' => OrderStatus::Completed, 'count' => 28, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Released],
            ['status' => OrderStatus::Delivered, 'count' => 10, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Holding],
            ['status' => OrderStatus::Processing, 'count' => 12, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Holding],
            ['status' => OrderStatus::Disputing, 'count' => 6, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Holding],
            ['status' => OrderStatus::Refunded, 'count' => 8, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Refunded],
            ['status' => OrderStatus::Cancelled, 'count' => 8, 'hasEscrow' => false],
        ];

        $maxItemsPerOrder = min(3, $activeListings->count());

        foreach ($orderConfigs as $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                $buyer = $buyers->random();
                $selectedListings = collect($activeListings->random(random_int(1, $maxItemsPerOrder)));
                $orderDate = Carbon::now()->subDays(random_int(14, 180))->subMinutes(random_int(0, 1440));
                $paymentMethod = fake()->randomElement([PaymentMethod::VNPay, PaymentMethod::Stripe]);

                $order = $this->createOrder($buyer, $orderDate, $paymentMethod);
                $totalPrice = 0;

                foreach ($selectedListings as $listing) {
                    $quantity = random_int(1, 2);
                    $subtotal = round(((float) $listing->price) * $quantity, 2);
                    $totalPrice += $subtotal;

                    $orderItem = $this->createOrderItem($order, $listing, $config['status'], $orderDate, $quantity, $subtotal);

                    if (in_array($config['status'], [OrderStatus::Completed, OrderStatus::Delivered, OrderStatus::Disputing, OrderStatus::Refunded], true)) {
                        $this->assignKeysToOrderItem($listing, $orderItem, $quantity, $config['status']);
                    }

                    if ($config['hasEscrow']) {
                        $this->createEscrow($orderItem, $listing->seller_id, $config['escrowStatus'], $orderDate, $subtotal);
                    }
                }

                $order->update([
                    'total_price'    => round($totalPrice, 2),
                    'payment_status' => $this->paymentStatusForOrder($config['status']),
                ]);

                $this->createTransaction($order, $paymentMethod, $config['status'], $orderDate);
                $this->createPaymentTransaction($order, $paymentMethod, $config['status'], $orderDate);
            }
        }

        $this->seedWithdrawTransactions();
    }

    protected function seedWithdrawTransactions(): void
    {
        $wallets = Wallet::with('seller.user')->whereNotNull('seller_id')->get();

        foreach ($wallets as $wallet) {
            $withdrawCount = random_int(1, 2);

            for ($i = 0; $i < $withdrawCount; $i++) {
                if (Withdraw::where('wallet_id', $wallet->id)->whereDate('created_at', now()->subDays($i + 1))->exists()) {
                    continue;
                }

                $withdrawDate = Carbon::now()->subDays(random_int(5, 120));
                $amount = fake()->randomFloat(0, 500_000, 5_000_000);
                $status = fake()->randomElement([
                    WithdrawStatus::Completed,
                    WithdrawStatus::Completed,
                    WithdrawStatus::Processing,
                    WithdrawStatus::Pending,
                ]);

                Withdraw::factory()
                    ->forWallet($wallet)
                    ->state([
                        'amount'       => $amount,
                        'status'       => $status,
                        'requested_by' => $wallet->seller?->user_id,
                        'created_at'   => $withdrawDate,
                        'updated_at'   => $withdrawDate,
                    ])
                    ->create();

                Transaction::factory()
                    ->forWallet($wallet)
                    ->state([
                        'payment_info' => [
                            'bank_name'      => fake()->randomElement(['Vietcombank', 'Techcombank', 'BIDV', 'ACB', 'Sacombank', 'MB Bank']),
                            'account_number' => fake()->numerify('##########'),
                            'account_holder' => $wallet->seller->user->username ?? 'Unknown',
                            'note'           => 'Withdrawal from KeyCove',
                        ],
                        'amount' => -$amount,
                        'status' => match ($status) {
                            WithdrawStatus::Completed => TransactionStatus::Completed,
                            default                   => TransactionStatus::Pending,
                        },
                        'created_at' => $withdrawDate,
                        'updated_at' => $withdrawDate,
                    ])
                    ->create();
            }
        }
    }

    protected function createOrder(User $buyer, Carbon $orderDate, PaymentMethod $paymentMethod): Order
    {
        return Order::factory()
            ->forBuyer($buyer)
            ->state([
                'order_code'     => 'ORD-'.$orderDate->format('Ymd').'-'.strtoupper(Str::random(5)),
                'total_price'    => 0,
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::Pending,
                'created_at'     => $orderDate,
                'updated_at'     => $orderDate,
            ])
            ->create();
    }

    protected function createOrderItem(
        Order $order,
        ProductListing $listing,
        OrderStatus $status,
        Carbon $orderDate,
        int $quantity,
        float $subtotal,
    ): OrderItem {
        $platformFee = round($subtotal * 0.1, 2);

        $orderItem = OrderItem::create([
            'order_id'              => $order->id,
            'listing_id'            => $listing->id,
            'seller_id'             => $listing->seller_id,
            'product_name_snapshot' => $listing->variant?->product?->name ?? 'Unknown Product',
            'variant_snapshot'      => [
                'variant_id' => $listing->variant_id,
                'region'     => $listing->variant?->region?->name,
                'platform'   => $listing->variant?->platform?->name,
                'os'         => $listing->variant?->operatingSystem?->name,
                'edition'    => $listing->variant?->edition,
            ],
            'quantity'      => $quantity,
            'unit_price'    => $listing->price,
            'subtotal'      => $subtotal,
            'platform_fee'  => $platformFee,
            'seller_amount' => round($subtotal - $platformFee, 2),
            'status'        => $status,
        ]);

        $orderItem->forceFill([
            'created_at' => $orderDate,
            'updated_at' => $orderDate,
        ])->saveQuietly();

        return $orderItem;
    }

    protected function assignKeysToOrderItem(ProductListing $listing, OrderItem $orderItem, int $quantity, OrderStatus $status): void
    {
        $availableKeys = ProductKey::where('listing_id', $listing->id)
            ->where('status', ProductKeyStatus::Available)
            ->limit($quantity)
            ->get();

        foreach ($availableKeys as $key) {
            $key->update([
                'order_item_id' => $orderItem->id,
                'status'        => $status === OrderStatus::Disputing
                    ? ProductKeyStatus::Reserved
                    : ProductKeyStatus::Sold,
            ]);
        }
    }

    protected function createEscrow(OrderItem $orderItem, ?int $sellerId, EscrowStatus $status, Carbon $orderDate, float $amount): void
    {
        $releaseDate = match ($status) {
            EscrowStatus::Holding  => Carbon::now()->addDays(random_int(1, 7)),
            EscrowStatus::Released => $orderDate->copy()->addDays(random_int(1, 3)),
            EscrowStatus::Refunded => $orderDate->copy()->addDays(random_int(1, 5)),
        };

        Escrow::factory()
            ->forOrderItem($orderItem)
            ->state([
                'seller_id'    => $sellerId,
                'amount'       => $amount,
                'release_date' => $releaseDate,
                'status'       => $status,
                'created_at'   => $orderDate,
                'updated_at'   => $orderDate,
            ])
            ->create();
    }

    protected function createTransaction(Order $order, PaymentMethod $paymentMethod, OrderStatus $status, Carbon $orderDate): void
    {
        Transaction::factory()
            ->forOrder($order)
            ->state([
                'type'         => TransactionType::PaymentReceived,
                'balance_type' => TransactionBalanceType::Available,
                'payment_info' => $this->generatePaymentInfo($paymentMethod),
                'amount'       => $status === OrderStatus::Cancelled ? 0 : $order->total_price,
                'status'       => $this->transactionStatusForOrder($status),
                'created_at'   => $orderDate,
                'updated_at'   => $orderDate,
            ])
            ->create();
    }

    protected function createPaymentTransaction(Order $order, PaymentMethod $paymentMethod, OrderStatus $status, Carbon $orderDate): void
    {
        PaymentTransaction::factory()
            ->for($order, 'order')
            ->state([
                'gateway'                => $paymentMethod,
                'gateway_transaction_id' => 'PAY'.Str::upper(Str::random(4)).$order->id,
                'amount'                 => $status === OrderStatus::Cancelled ? 0 : $order->total_price,
                'status'                 => $this->paymentStatusForOrder($status),
                'request_payload'        => $this->generatePaymentInfo($paymentMethod),
                'response_payload'       => ['status' => $this->paymentStatusForOrder($status)->name],
                'paid_at'                => in_array($status, [OrderStatus::Cancelled], true) ? null : $orderDate,
                'created_at'             => $orderDate,
                'updated_at'             => $orderDate,
            ])
            ->create();
    }

    protected function paymentStatusForOrder(OrderStatus $status): PaymentStatus
    {
        return match ($status) {
            OrderStatus::Cancelled => PaymentStatus::Cancelled,
            OrderStatus::Refunded  => PaymentStatus::Refunded,
            default                => PaymentStatus::Completed,
        };
    }

    protected function transactionStatusForOrder(OrderStatus $status): TransactionStatus
    {
        return match ($status) {
            OrderStatus::Cancelled => TransactionStatus::Cancelled,
            default                => TransactionStatus::Completed,
        };
    }

    protected function generatePaymentInfo(PaymentMethod $method): array
    {
        if ($method === PaymentMethod::VNPay) {
            return [
                'method'         => 'VNPay',
                'transaction_id' => 'VNP'.fake()->numerify('##############'),
                'bank_code'      => fake()->randomElement(['NCB', 'VIETCOMBANK', 'TECHCOMBANK', 'SACOMBANK', 'BIDV', 'MB', 'ACB']),
                'card_type'      => fake()->randomElement(['ATM', 'VISA', 'MASTERCARD', 'JCB']),
                'response_code'  => '00',
            ];
        }

        return [
            'method'            => 'Stripe',
            'transaction_id'    => 'pi_'.fake()->bothify('????####################'),
            'payment_method_id' => 'pm_'.fake()->bothify('????####################'),
            'card_brand'        => fake()->randomElement(['visa', 'mastercard', 'amex']),
            'last4'             => fake()->numerify('####'),
        ];
    }
}
