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
        $activeListings = ProductListing::with(['variant', 'seller'])
            ->where('status', ProductListingStatus::Active)
            ->get();

        if ($buyers->isEmpty() || $activeListings->isEmpty()) {
            return;
        }

        // Order distribution: 45 completed, 10 processing, 8 delivered, 5 disputing, 7 cancelled, 5 refunded
        $orderConfigs = [
            ['status' => OrderStatus::Completed, 'count' => 45, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Released],
            ['status' => OrderStatus::Processing, 'count' => 10, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Holding],
            ['status' => OrderStatus::Delivered, 'count' => 8, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Holding],
            ['status' => OrderStatus::Disputing, 'count' => 5, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Holding],
            ['status' => OrderStatus::Cancelled, 'count' => 7, 'hasEscrow' => false],
            ['status' => OrderStatus::Refunded, 'count' => 5, 'hasEscrow' => true, 'escrowStatus' => EscrowStatus::Refunded],
        ];

        $dayCounter = 0;

        foreach ($orderConfigs as $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                $buyer = $buyers->random();
                $numItems = random_int(1, 3);
                $selectedListings = $activeListings->random($numItems);

                // Create order
                $orderDate = Carbon::now()->subDays(random_int(1, 60))->subMinutes(random_int(0, 1440));
                $paymentMethod = fake()->randomElement([PaymentMethod::VNPay, PaymentMethod::Stripe]);

                $order = Order::create([
                    'buyer_id' => $buyer->id,
                    'order_code' => 'ORD-'.$orderDate->format('Ymd').'-'.strtoupper(Str::random(5)),
                    'total_price' => 0, // Will be calculated after items
                    'status' => $config['status'],
                    'payment_method' => $paymentMethod,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                $totalPrice = 0;

                // Create order items
                foreach ($selectedListings as $listing) {
                    $quantity = random_int(1, 2);
                    $subtotal = $listing->price * $quantity;
                    $totalPrice += $subtotal;

                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'listing_id' => $listing->id,
                        'product_name_snapshot' => $listing->variant?->product?->name ?? 'Unknown Product',
                        'quantity' => $quantity,
                        'unit_price' => $listing->price,
                        'subtotal' => $subtotal,
                        'created_at' => $orderDate,
                    ]);

                    // Link keys to order item if order is completed/delivered/disputing
                    if (in_array($config['status'], [OrderStatus::Completed, OrderStatus::Delivered, OrderStatus::Disputing])) {
                        $availableKeys = ProductKey::where('listing_id', $listing->id)
                            ->where('status', ProductKeyStatus::Available)
                            ->limit($quantity)
                            ->get();

                        foreach ($availableKeys as $key) {
                            $key->update([
                                'order_item_id' => $orderItem->id,
                                'status' => $config['status'] === OrderStatus::Disputing
                                    ? ProductKeyStatus::Pending
                                    : ProductKeyStatus::Sold,
                            ]);
                        }
                    }
                }

                // Update order total
                $order->update(['total_price' => round($totalPrice, 2)]);

                // Create escrow if needed
                if ($config['hasEscrow']) {
                    $escrowDate = $orderDate;
                    $escrowStatus = $config['escrowStatus'];

                    $releaseDate = match ($escrowStatus) {
                        EscrowStatus::Holding => Carbon::now()->addDays(random_int(1, 7)),
                        EscrowStatus::Released => $orderDate->copy()->addDays(random_int(1, 3)),
                        EscrowStatus::Refunded => $orderDate->copy()->addDays(random_int(1, 5)),
                    };

                    Escrow::create([
                        'order_id' => $order->id,
                        'amount' => $order->total_price,
                        'release_date' => $releaseDate,
                        'status' => $escrowStatus,
                        'created_at' => $escrowDate,
                        'updated_at' => $escrowDate,
                    ]);
                }

                // Create payment transaction
                Transaction::create([
                    'order_id' => $order->id,
                    'wallet_id' => null,
                    'type' => TransactionType::Pay,
                    'payment_info' => $this->generatePaymentInfo($paymentMethod),
                    'amount' => $order->total_price,
                    'status' => $config['status'] === OrderStatus::Cancelled
                        ? TransactionStatus::Cancelled
                        : TransactionStatus::Completed,
                    'created_at' => $orderDate,
                ]);

                $dayCounter++;
            }
        }

        // Create some withdrawal transactions for sellers
        $this->seedWithdrawTransactions();
    }

    protected function seedWithdrawTransactions(): void
    {
        $wallets = Wallet::with('seller')->get();

        foreach ($wallets as $wallet) {
            $numWithdrawals = random_int(2, 5);

            for ($i = 0; $i < $numWithdrawals; $i++) {
                $withdrawDate = Carbon::now()->subDays(random_int(1, 45));
                $amount = fake()->randomFloat(2, 100, 5000);
                $status = fake()->randomElement([
                    WithdrawStatus::Completed,
                    WithdrawStatus::Completed,
                    WithdrawStatus::Completed,
                    WithdrawStatus::Processing,
                    WithdrawStatus::Pending,
                ]);

                // Check if wallet already has a withdraw (1:1 relationship)
                $existingWithdraw = Withdraw::where('wallet_id', $wallet->id)->first();

                if ($existingWithdraw) {
                    continue;
                }

                $withdraw = Withdraw::create([
                    'wallet_id' => $wallet->id,
                    'amount' => $amount,
                    'status' => $status,
                    'created_at' => $withdrawDate,
                    'updated_at' => $withdrawDate,
                ]);

                Transaction::create([
                    'order_id' => null,
                    'wallet_id' => $wallet->id,
                    'type' => TransactionType::Withdraw,
                    'payment_info' => [
                        'bank_name' => fake()->randomElement(['Vietcombank', 'Techcombank', 'BIDV', 'ACB', 'Sacombank', 'MB Bank']),
                        'account_number' => fake()->numerify('##########'),
                        'account_holder' => $wallet->seller->user->username ?? 'Unknown',
                        'note' => 'Withdrawal from KeyCove',
                    ],
                    'amount' => $amount,
                    'status' => match ($status) {
                        WithdrawStatus::Completed => TransactionStatus::Completed,
                        default => TransactionStatus::Pending,
                    },
                    'created_at' => $withdrawDate,
                ]);
            }
        }
    }

    protected function generatePaymentInfo(PaymentMethod $method): array
    {
        if ($method === PaymentMethod::VNPay) {
            return [
                'method' => 'VNPay',
                'transaction_id' => 'VNP'.fake()->numerify('##############'),
                'bank_code' => fake()->randomElement(['NCB', 'VIETCOMBANK', 'TECHCOMBANK', 'SACOMBANK', 'BIDV', 'MB', 'ACB']),
                'card_type' => fake()->randomElement(['ATM', 'VISA', 'MASTERCARD', 'JCB']),
                'response_code' => '00',
            ];
        }

        return [
            'method' => 'Stripe',
            'transaction_id' => 'pi_'.fake()->bothify('????####################'),
            'payment_method_id' => 'pm_'.fake()->bothify('????####################'),
            'card_brand' => fake()->randomElement(['visa', 'mastercard', 'amex']),
            'last4' => fake()->numerify('####'),
        ];
    }
}
