<?php

namespace Database\Seeders;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Enums\WithdrawStatus;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderItem;
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
        $activeListings = ProductListing::with(['variant.product', 'seller'])
            ->where('status', ProductListingStatus::Active)
            ->get();

        if ($buyers->isEmpty() || $activeListings->isEmpty()) {
            return;
        }

        $orderConfigs = [
            ['status' => OrderStatus::Completed, 'count' => 45, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Released],
            ['status' => OrderStatus::Processing, 'count' => 10, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Holding],
            ['status' => OrderStatus::Delivered, 'count' => 8, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Holding],
            ['status' => OrderStatus::Disputing, 'count' => 5, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Holding],
            ['status' => OrderStatus::Cancelled, 'count' => 7, 'hasEscrow' => false],
            ['status' => OrderStatus::Refunded, 'count' => 5, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Refunded],
        ];

        $maxItemsPerOrder = min(3, $activeListings->count());

        foreach ($orderConfigs as $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                $buyer = $buyers->random();
                $numItems = random_int(1, $maxItemsPerOrder);
                $selectedListings = collect($activeListings->random($numItems));

                $orderDate = Carbon::now()->subDays(random_int(1, 60))->subMinutes(random_int(0, 1440));
                $paymentMethod = fake()->randomElement([PaymentMethod::VNPay, PaymentMethod::Stripe]);

                $order = $this->createOrder($buyer, $orderDate, $paymentMethod);

                $totalPrice = 0;

                foreach ($selectedListings as $listing) {
                    $quantity = random_int(1, 2);
                    $subtotal = round(((float) $listing->price) * $quantity, 2);
                    $totalPrice += $subtotal;

                    $orderItem = $this->createOrderItem($order, $listing, $config['status'], $orderDate, $quantity, $subtotal);

                    if (in_array($config['status'], [OrderStatus::Completed, OrderStatus::Delivered, OrderStatus::Disputing, OrderStatus::Refunded], true)) {
                        $availableKeys = ProductKey::where('listing_id', $listing->id)
                            ->where('status', ProductKeyStatus::Available)
                            ->limit($quantity)
                            ->get();

                        foreach ($availableKeys as $key) {
                            $key->update([
                                'order_item_id' => $orderItem->id,
                                'status'        => $config['status'] === OrderStatus::Disputing
                                    ? ProductKeyStatus::Pending
                                    : ProductKeyStatus::Sold,
                            ]);
                        }
                    }

                    if ($config['hasEscrow']) {
                        $this->createEscrow($orderItem, $listing->seller_id, $config['escrowStatus'], $orderDate, $subtotal);
                    }
                }

                $order->update(['total_price' => round($totalPrice, 2)]);

                $this->createTransaction($order, $paymentMethod, $config['status'], $orderDate);
            }
        }

        $this->seedWithdrawTransactions();
    }

    protected function seedWithdrawTransactions(): void
    {
        $wallets = Wallet::with('seller.user')->get();

        foreach ($wallets as $wallet) {
            if (Withdraw::where('wallet_id', $wallet->id)->exists()) {
                continue;
            }

            $withdrawDate = Carbon::now()->subDays(random_int(1, 45));
            $amount = fake()->randomFloat(2, 100, 5000);
            $status = fake()->randomElement([
                WithdrawStatus::Completed,
                WithdrawStatus::Completed,
                WithdrawStatus::Completed,
                WithdrawStatus::Processing,
                WithdrawStatus::Pending,
            ]);

            Withdraw::factory()
                ->forWallet($wallet)
                ->state([
                    'amount'     => $amount,
                    'status'     => $status,
                    'created_at' => $withdrawDate,
                    'updated_at' => $withdrawDate,
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
                    'amount' => $amount,
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

    protected function createOrder(User $buyer, Carbon $orderDate, PaymentMethod $paymentMethod): Order
    {
        return Order::factory()
            ->forBuyer($buyer)
            ->state([
                'order_code'     => 'ORD-'.$orderDate->format('Ymd').'-'.strtoupper(Str::random(5)),
                'total_price'    => 0,
                'payment_method' => $paymentMethod,
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
        return OrderItem::factory()
            ->forOrder($order)
            ->state([
                'listing_id'            => $listing->id,
                'product_name_snapshot' => $listing->variant?->product?->name ?? 'Unknown Product',
                'quantity'              => $quantity,
                'unit_price'            => $listing->price,
                'subtotal'              => $subtotal,
                'status'                => $status,
                'created_at'            => $orderDate,
                'updated_at'            => $orderDate,
            ])
            ->create();
    }

    protected function createEscrow(OrderItem $orderItem, int $sellerId, EscrowStatus $status, Carbon $orderDate, float $amount): void
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
                'wallet_id'    => null,
                'type'         => TransactionType::Pay,
                'payment_info' => $this->generatePaymentInfo($paymentMethod),
                'amount'       => $order->total_price,
                'status'       => $status === OrderStatus::Cancelled
                    ? TransactionStatus::Cancelled
                    : TransactionStatus::Completed,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ])
            ->create();
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
