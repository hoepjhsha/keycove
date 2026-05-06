<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EscrowStatus;
use App\Enums\InternalWalletDirection;
use App\Enums\InternalWalletEntryType;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PlatformPayoutStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\WalletType;
use App\Enums\WithdrawStatus;
use App\Models\Escrow;
use App\Models\InternalWalletEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\PlatformPayout;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\Seller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdraw;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    private const ORDER_COUNT = 1480;

    private const PLATFORM_FEE_RATE = 0.10;

    /** @var Collection<int, User> */
    private Collection $buyers;

    /** @var Collection<int, User> */
    private Collection $buyerPool;

    /** @var Collection<int, Seller> */
    private Collection $sellers;

    /** @var Collection<int, ProductListing> */
    private Collection $listings;

    /** @var array<int, int> */
    private array $stockLevels = [];

    /** @var array<int, array{balance: float, holding: float}> */
    private array $walletTotals = [];

    private float $internalWalletBalance = 0.0;

    private Wallet $internalWallet;

    private User $adminUser;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->buyers = User::query()
            ->where('role', UserRole::User)
            ->where('status', UserStatus::Active)
            ->orderBy('created_at')
            ->get();

        $this->sellers = Seller::query()
            ->with(['user', 'wallet'])
            ->where('kyc_status', KycStatus::Approved)
            ->get();

        $this->listings = ProductListing::query()
            ->with(['variant.product', 'variant.region', 'variant.platform', 'variant.operatingSystem', 'seller.user', 'seller.wallet'])
            ->where('status', ProductListingStatus::Active)
            ->where('stock_count', '>', 0)
            ->get();

        $this->adminUser = User::query()
            ->whereIn('role', [UserRole::SuperAdmin, UserRole::Admin])
            ->orderBy('id')
            ->firstOrFail();

        $this->internalWallet = Wallet::firstOrCreate(
            ['type' => WalletType::Internal],
            [
                'seller_id' => null,
                'type'      => WalletType::Internal,
                'code'      => 'KC-INT-'.now()->format('Ymd'),
                'balance'   => 0,
                'holding'   => 0,
            ]
        );

        $this->buyerPool = $this->buildBuyerPool();
        $this->stockLevels = $this->listings->mapWithKeys(fn (ProductListing $listing): array => [$listing->id => (int) $listing->stock_count])->all();

        foreach ($this->sellers as $seller) {
            if ($seller->wallet === null) {
                continue;
            }

            $this->walletTotals[$seller->wallet->id] = [
                'balance' => 0.0,
                'holding' => 0.0,
            ];
        }

        $this->seedOrders();
        $this->seedWithdrawals();
        $this->seedPlatformPayouts();
        $this->syncDerivedBalances();
    }

    /**
     * @return Collection<int, User>
     */
    protected function buildBuyerPool(): Collection
    {
        $sortedBuyers = $this->buyers->values();
        $total = max(1, $sortedBuyers->count());

        return $sortedBuyers->flatMap(function (User $buyer, int $index) use ($total): array {
            $ratio = ($index + 1) / $total;
            $weight = match (true) {
                $ratio <= 0.10 => 12,
                $ratio <= 0.35 => 6,
                $ratio <= 0.65 => 3,
                default        => 1,
            };

            return array_fill(0, $weight, $buyer);
        })->values();
    }

    protected function seedOrders(): void
    {
        for ($index = 0; $index < self::ORDER_COUNT; $index++) {
            $createdAt = $this->makeOrderTimestamp($index);
            $status = $this->resolveOrderStatus($createdAt);
            $buyer = $this->buyerPool->random();
            $selectedListings = $this->selectListingsForOrder();

            if ($selectedListings->isEmpty()) {
                break;
            }

            $order = Order::create([
                'buyer_id'       => $buyer->id,
                'order_code'     => $this->makeOrderCode($createdAt),
                'total_price'    => 0,
                'payment_method' => PaymentMethod::VNPay,
                'payment_status' => $this->resolvePaymentStatus($status),
                'created_at'     => $createdAt,
                'updated_at'     => $this->resolveOrderUpdatedAt($createdAt, $status),
            ]);

            $totalPrice = 0.0;

            foreach ($selectedListings as $listing) {
                $totalPrice += $this->createOrderItem($order, $listing, $status, $createdAt);
            }

            if ($totalPrice <= 0) {
                $order->delete();

                continue;
            }

            $order->updateQuietly(['total_price' => $totalPrice]);
            $this->createPaymentLedger($order, $status, $createdAt, $totalPrice);
        }
    }

    /**
     * @return Collection<int, ProductListing>
     */
    protected function selectListingsForOrder(): Collection
    {
        $availableListings = $this->listings
            ->filter(fn (ProductListing $listing): bool => ($this->stockLevels[$listing->id] ?? 0) > 0)
            ->values();

        if ($availableListings->isEmpty()) {
            return collect();
        }

        $count = fake()->randomElement([1, 1, 1, 2, 2, 3]);

        return $availableListings
            ->shuffle()
            ->take(min($count, $availableListings->count()))
            ->values();
    }

    protected function createOrderItem(Order $order, ProductListing $listing, OrderStatus $status, Carbon $createdAt): float
    {
        $quantity = $this->resolveQuantity($listing);
        $unitPrice = (float) $listing->price;
        $subtotal = $unitPrice * $quantity;
        $platformFee = $listing->seller_id === null ? 0.0 : round($subtotal * self::PLATFORM_FEE_RATE, 2);
        $sellerAmount = round($subtotal - $platformFee, 2);
        $deliveredAt = $this->resolveDeliveredAt($createdAt, $status);
        $completedAt = $this->resolveCompletedAt($createdAt, $status);

        $orderItem = OrderItem::create([
            'order_id'              => $order->id,
            'listing_id'            => $listing->id,
            'seller_id'             => $listing->seller_id,
            'order_item_code'       => $this->makeOrderItemCode($createdAt),
            'product_name_snapshot' => $listing->variant->product->name,
            'variant_snapshot'      => [
                'edition'          => $listing->variant->edition,
                'region'           => $listing->variant->region?->flag_code,
                'platform'         => $listing->variant->platform?->name,
                'operating_system' => $listing->variant->operatingSystem?->name,
            ],
            'quantity'            => $quantity,
            'unit_price'          => $unitPrice,
            'subtotal'            => $subtotal,
            'platform_fee'        => $platformFee,
            'seller_amount'       => $sellerAmount,
            'status'              => $status,
            'delivered_at'        => $deliveredAt,
            'completed_at'        => $completedAt,
            'buyer_key_viewed_at' => $this->resolveBuyerKeyViewedAt($createdAt, $status),
            'created_at'          => $createdAt,
            'updated_at'          => $this->resolveOrderUpdatedAt($createdAt, $status),
        ]);

        if ($this->statusConsumesInventory($status)) {
            $assignedKeys = ProductKey::query()
                ->where('listing_id', $listing->id)
                ->where('status', ProductKeyStatus::Available)
                ->orderBy('id')
                ->limit($quantity)
                ->get();

            if ($assignedKeys->count() < $quantity) {
                $orderItem->delete();

                return 0.0;
            }

            foreach ($assignedKeys as $productKey) {
                $productKey->updateQuietly([
                    'order_item_id' => $orderItem->id,
                    'status'        => $status === OrderStatus::Refunded ? ProductKeyStatus::Refunded : ProductKeyStatus::Sold,
                    'updated_at'    => $this->resolveOrderUpdatedAt($createdAt, $status),
                ]);
            }

            $this->stockLevels[$listing->id] -= $quantity;
        }

        $this->createEscrowAndWalletFlows($order, $orderItem, $listing, $status, $createdAt);

        return $subtotal;
    }

    protected function createEscrowAndWalletFlows(Order $order, OrderItem $orderItem, ProductListing $listing, OrderStatus $status, Carbon $createdAt): void
    {
        if ($listing->seller_id === null) {
            $this->createPlatformOwnedWalletFlows($order, $orderItem, $status, $createdAt);

            return;
        }

        if ($listing->seller === null || $listing->seller->wallet === null) {
            return;
        }

        if (! in_array($status, [OrderStatus::Processing, OrderStatus::Delivered, OrderStatus::Completed, OrderStatus::Disputing, OrderStatus::Refunded], true)) {
            return;
        }

        $sellerWallet = $listing->seller->wallet;
        $escrowStatus = match ($status) {
            OrderStatus::Completed => EscrowStatus::Released,
            OrderStatus::Disputing => EscrowStatus::Frozen,
            OrderStatus::Refunded  => EscrowStatus::Refunded,
            default                => EscrowStatus::Holding,
        };
        $releaseDate = $createdAt->copy()->addDays(random_int(3, 10));

        $escrow = Escrow::create([
            'order_item_id' => $orderItem->id,
            'seller_id'     => $listing->seller_id,
            'amount'        => $orderItem->seller_amount,
            'release_date'  => $releaseDate,
            'status'        => $escrowStatus,
            'created_at'    => $createdAt,
            'updated_at'    => $this->resolveOrderUpdatedAt($createdAt, $status),
        ]);

        if ($status === OrderStatus::Completed) {
            $this->walletTotals[$sellerWallet->id]['balance'] += (float) $orderItem->seller_amount;

            Transaction::create([
                'wallet_id'       => $sellerWallet->id,
                'order_id'        => $order->id,
                'source_type'     => Escrow::class,
                'source_id'       => $escrow->id,
                'type'            => TransactionType::EscrowRelease,
                'balance_type'    => TransactionBalanceType::Available,
                'payment_info'    => null,
                'amount'          => $orderItem->seller_amount,
                'status'          => TransactionStatus::Completed,
                'idempotency_key' => 'seller-release-'.$escrow->id,
                'metadata'        => ['order_item_id' => $orderItem->id],
                'created_at'      => $releaseDate,
                'updated_at'      => $releaseDate,
            ]);

            InternalWalletEntry::create([
                'wallet_id'       => $this->internalWallet->id,
                'order_id'        => $order->id,
                'source_type'     => Escrow::class,
                'source_id'       => $escrow->id,
                'type'            => InternalWalletEntryType::EscrowReleased,
                'direction'       => InternalWalletDirection::Neutral,
                'amount'          => $orderItem->seller_amount,
                'status'          => TransactionStatus::Completed,
                'affects_balance' => false,
                'idempotency_key' => 'internal-release-'.$escrow->id,
                'metadata'        => ['seller_id' => $listing->seller_id],
                'occurred_at'     => $releaseDate,
                'created_at'      => $releaseDate,
                'updated_at'      => $releaseDate,
            ]);

            return;
        }

        if (in_array($status, [OrderStatus::Processing, OrderStatus::Delivered, OrderStatus::Disputing], true)) {
            $this->walletTotals[$sellerWallet->id]['holding'] += (float) $orderItem->seller_amount;

            Transaction::create([
                'wallet_id'       => $sellerWallet->id,
                'order_id'        => $order->id,
                'source_type'     => Escrow::class,
                'source_id'       => $escrow->id,
                'type'            => TransactionType::EscrowHold,
                'balance_type'    => TransactionBalanceType::Holding,
                'payment_info'    => null,
                'amount'          => $orderItem->seller_amount,
                'status'          => TransactionStatus::Completed,
                'idempotency_key' => 'seller-hold-'.$escrow->id,
                'metadata'        => ['order_item_id' => $orderItem->id],
                'created_at'      => $createdAt,
                'updated_at'      => $createdAt,
            ]);

            InternalWalletEntry::create([
                'wallet_id'       => $this->internalWallet->id,
                'order_id'        => $order->id,
                'source_type'     => Escrow::class,
                'source_id'       => $escrow->id,
                'type'            => InternalWalletEntryType::EscrowHeld,
                'direction'       => InternalWalletDirection::Neutral,
                'amount'          => $orderItem->seller_amount,
                'status'          => TransactionStatus::Completed,
                'affects_balance' => false,
                'idempotency_key' => 'internal-hold-'.$escrow->id,
                'metadata'        => ['seller_id' => $listing->seller_id],
                'occurred_at'     => $createdAt,
                'created_at'      => $createdAt,
                'updated_at'      => $createdAt,
            ]);

            return;
        }

        InternalWalletEntry::create([
            'wallet_id'       => $this->internalWallet->id,
            'order_id'        => $order->id,
            'source_type'     => Escrow::class,
            'source_id'       => $escrow->id,
            'type'            => InternalWalletEntryType::RefundPaid,
            'direction'       => InternalWalletDirection::Outflow,
            'amount'          => $orderItem->subtotal,
            'status'          => TransactionStatus::Completed,
            'affects_balance' => true,
            'idempotency_key' => 'internal-refund-'.$escrow->id,
            'metadata'        => ['seller_id' => $listing->seller_id],
            'occurred_at'     => $this->resolveOrderUpdatedAt($createdAt, $status),
            'created_at'      => $this->resolveOrderUpdatedAt($createdAt, $status),
            'updated_at'      => $this->resolveOrderUpdatedAt($createdAt, $status),
        ]);

        $this->internalWalletBalance -= (float) $orderItem->subtotal;
    }

    protected function createPlatformOwnedWalletFlows(Order $order, OrderItem $orderItem, OrderStatus $status, Carbon $createdAt): void
    {
        if (! in_array($status, [OrderStatus::Processing, OrderStatus::Delivered, OrderStatus::Completed, OrderStatus::Disputing, OrderStatus::Refunded], true)) {
            return;
        }

        $occurredAt = $this->resolveOrderUpdatedAt($createdAt, $status);

        Transaction::create([
            'wallet_id'       => $this->internalWallet->id,
            'order_id'        => $order->id,
            'source_type'     => OrderItem::class,
            'source_id'       => $orderItem->id,
            'type'            => TransactionType::PaymentReceived,
            'balance_type'    => TransactionBalanceType::Available,
            'payment_info'    => ['gateway' => 'vnpay', 'owner' => 'platform'],
            'amount'          => $orderItem->seller_amount,
            'status'          => TransactionStatus::Completed,
            'idempotency_key' => 'internal-shop-sale-'.$orderItem->id,
            'metadata'        => ['order_item_id' => $orderItem->id],
            'created_at'      => $occurredAt,
            'updated_at'      => $occurredAt,
        ]);

        if ($status !== OrderStatus::Refunded) {
            return;
        }

        Transaction::create([
            'wallet_id'       => $this->internalWallet->id,
            'order_id'        => $order->id,
            'source_type'     => OrderItem::class,
            'source_id'       => $orderItem->id,
            'type'            => TransactionType::Refund,
            'balance_type'    => TransactionBalanceType::Available,
            'payment_info'    => ['gateway' => 'vnpay', 'owner' => 'platform'],
            'amount'          => -((float) $orderItem->subtotal),
            'status'          => TransactionStatus::Completed,
            'idempotency_key' => 'internal-shop-refund-'.$orderItem->id,
            'metadata'        => ['order_item_id' => $orderItem->id],
            'created_at'      => $occurredAt,
            'updated_at'      => $occurredAt,
        ]);

        InternalWalletEntry::create([
            'wallet_id'       => $this->internalWallet->id,
            'order_id'        => $order->id,
            'source_type'     => OrderItem::class,
            'source_id'       => $orderItem->id,
            'type'            => InternalWalletEntryType::RefundPaid,
            'direction'       => InternalWalletDirection::Outflow,
            'amount'          => $orderItem->subtotal,
            'status'          => TransactionStatus::Completed,
            'affects_balance' => true,
            'idempotency_key' => 'internal-shop-refund-entry-'.$orderItem->id,
            'metadata'        => ['owner' => 'platform'],
            'occurred_at'     => $occurredAt,
            'created_at'      => $occurredAt,
            'updated_at'      => $occurredAt,
        ]);

        $this->internalWalletBalance -= (float) $orderItem->subtotal;
    }

    protected function createPaymentLedger(Order $order, OrderStatus $status, Carbon $createdAt, float $totalPrice): void
    {
        $paymentStatus = match ($status) {
            OrderStatus::PendingPayment => PaymentStatus::Pending,
            OrderStatus::Cancelled      => fake()->boolean(65) ? PaymentStatus::Failed : PaymentStatus::Cancelled,
            OrderStatus::Refunded       => PaymentStatus::Refunded,
            default                     => PaymentStatus::Completed,
        };

        $paidAt = $paymentStatus === PaymentStatus::Completed || $paymentStatus === PaymentStatus::Refunded
            ? $createdAt->copy()->addMinutes(random_int(3, 180))
            : null;
        $txnRef = 'KC'.now()->format('ym').str_pad((string) $order->id, 8, '0', STR_PAD_LEFT);
        $gatewayTransactionId = 'VNP'.strtoupper(Str::random(12)).$order->id;

        PaymentTransaction::create([
            'order_id'               => $order->id,
            'gateway'                => PaymentMethod::VNPay,
            'gateway_transaction_id' => $gatewayTransactionId,
            'amount'                 => $totalPrice,
            'status'                 => $paymentStatus,
            'request_payload'        => [
                'vnp_Amount'     => (int) round($totalPrice * 100),
                'vnp_BankCode'   => $this->randomBankCode(),
                'vnp_Command'    => 'pay',
                'vnp_CreateDate' => $createdAt->format('YmdHis'),
                'vnp_IpAddr'     => fake()->ipv4(),
                'vnp_OrderInfo'  => 'Thanh toan don hang '.$order->order_code,
                'vnp_TmnCode'    => 'KCOVETM1',
                'vnp_TxnRef'     => $txnRef,
            ],
            'response_payload' => [
                'vnp_BankCode'      => $this->randomBankCode(),
                'vnp_BankTranNo'    => fake()->numerify('###########'),
                'vnp_CardType'      => fake()->randomElement(['ATM', 'VNPAYQR', 'IB']),
                'vnp_PayDate'       => $paidAt?->format('YmdHis'),
                'vnp_ResponseCode'  => $this->responseCodeForPaymentStatus($paymentStatus),
                'vnp_TransactionNo' => fake()->numerify('##########'),
                'vnp_TxnRef'        => $txnRef,
            ],
            'paid_at'    => $paidAt,
            'created_at' => $createdAt,
            'updated_at' => $paidAt ?? $createdAt,
        ]);

        if (! in_array($paymentStatus, [PaymentStatus::Completed, PaymentStatus::Refunded], true)) {
            return;
        }

        Transaction::create([
            'wallet_id'       => $this->internalWallet->id,
            'order_id'        => $order->id,
            'source_type'     => Order::class,
            'source_id'       => $order->id,
            'type'            => TransactionType::PaymentReceived,
            'balance_type'    => TransactionBalanceType::Available,
            'payment_info'    => ['gateway' => 'vnpay', 'txn_ref' => $txnRef],
            'amount'          => $totalPrice,
            'status'          => TransactionStatus::Completed,
            'idempotency_key' => 'internal-payment-'.$order->id,
            'metadata'        => ['payment_status' => $paymentStatus->value],
            'created_at'      => $paidAt ?? $createdAt,
            'updated_at'      => $paidAt ?? $createdAt,
        ]);

        InternalWalletEntry::create([
            'wallet_id'       => $this->internalWallet->id,
            'order_id'        => $order->id,
            'source_type'     => Order::class,
            'source_id'       => $order->id,
            'type'            => InternalWalletEntryType::PaymentReceived,
            'direction'       => InternalWalletDirection::Inflow,
            'amount'          => $totalPrice,
            'status'          => TransactionStatus::Completed,
            'affects_balance' => true,
            'idempotency_key' => 'internal-entry-payment-'.$order->id,
            'metadata'        => ['payment_status' => $paymentStatus->value],
            'occurred_at'     => $paidAt ?? $createdAt,
            'created_at'      => $paidAt ?? $createdAt,
            'updated_at'      => $paidAt ?? $createdAt,
        ]);

        $this->internalWalletBalance += $totalPrice;
    }

    protected function seedWithdrawals(): void
    {
        $eligibleSellers = $this->sellers
            ->filter(function (Seller $seller): bool {
                $wallet = $seller->wallet;

                return $wallet !== null
                    && ($this->walletTotals[$wallet->id]['balance'] ?? 0.0) >= 1_500_000;
            })
            ->values();

        foreach ($eligibleSellers as $seller) {
            $wallet = $seller->wallet;

            if ($wallet === null) {
                continue;
            }

            $withdrawCount = fake()->numberBetween(2, 5);

            for ($i = 0; $i < $withdrawCount; $i++) {
                $availableBalance = $this->walletTotals[$wallet->id]['balance'] ?? 0.0;

                if ($availableBalance < 700_000) {
                    break;
                }

                $status = fake()->randomElement([
                    WithdrawStatus::Completed,
                    WithdrawStatus::Completed,
                    WithdrawStatus::Completed,
                    WithdrawStatus::Pending,
                    WithdrawStatus::Rejected,
                ]);
                $amount = min(
                    round($availableBalance * fake()->randomFloat(2, 0.08, 0.28), -3),
                    $availableBalance - 350_000
                );

                if ($amount < 500_000) {
                    continue;
                }

                $createdAt = now()->subDays(random_int(1, 150))->subMinutes(random_int(0, 1440));
                $processedAt = $status === WithdrawStatus::Completed || $status === WithdrawStatus::Rejected
                    ? $createdAt->copy()->addHours(random_int(4, 72))
                    : null;

                $withdraw = Withdraw::create([
                    'wallet_id'           => $wallet->id,
                    'amount'              => $amount,
                    'status'              => $status,
                    'bank_name'           => fake()->randomElement(['Vietcombank', 'Techcombank', 'BIDV', 'ACB', 'MB Bank', 'Sacombank']),
                    'bank_account_number' => fake()->numerify('############'),
                    'bank_account_name'   => $seller->user?->profile?->first_name
                        ? trim($seller->user->profile->first_name.' '.$seller->user->profile->last_name)
                        : strtoupper($seller->shop_name),
                    'requested_by'  => $seller->user_id,
                    'processed_by'  => $status === WithdrawStatus::Pending ? null : $this->adminUser->id,
                    'processed_at'  => $processedAt,
                    'reject_reason' => $status === WithdrawStatus::Rejected ? 'Thong tin tai khoan ngan hang can duoc xac minh lai.' : null,
                    'metadata'      => ['seeded' => true],
                    'created_at'    => $createdAt,
                    'updated_at'    => $processedAt ?? $createdAt,
                ]);

                InternalWalletEntry::create([
                    'wallet_id'   => $this->internalWallet->id,
                    'order_id'    => null,
                    'source_type' => Withdraw::class,
                    'source_id'   => $withdraw->id,
                    'type'        => $status === WithdrawStatus::Completed
                        ? InternalWalletEntryType::SellerPayoutCompleted
                        : ($status === WithdrawStatus::Rejected ? InternalWalletEntryType::SellerPayoutFailed : InternalWalletEntryType::SellerPayoutRequested),
                    'direction'       => $status === WithdrawStatus::Completed ? InternalWalletDirection::Outflow : InternalWalletDirection::Neutral,
                    'amount'          => $amount,
                    'status'          => TransactionStatus::Completed,
                    'affects_balance' => $status === WithdrawStatus::Completed,
                    'idempotency_key' => 'internal-withdraw-'.$withdraw->id,
                    'metadata'        => ['seller_id' => $seller->id],
                    'occurred_at'     => $processedAt ?? $createdAt,
                    'created_at'      => $processedAt ?? $createdAt,
                    'updated_at'      => $processedAt ?? $createdAt,
                ]);

                if ($status !== WithdrawStatus::Completed) {
                    continue;
                }

                $this->walletTotals[$wallet->id]['balance'] -= $amount;
                $this->internalWalletBalance -= $amount;

                Transaction::create([
                    'wallet_id'    => $wallet->id,
                    'order_id'     => null,
                    'source_type'  => Withdraw::class,
                    'source_id'    => $withdraw->id,
                    'type'         => TransactionType::Withdraw,
                    'balance_type' => TransactionBalanceType::Available,
                    'payment_info' => [
                        'bank_name'         => $withdraw->bank_name,
                        'bank_account_name' => $withdraw->bank_account_name,
                    ],
                    'amount'          => -$amount,
                    'status'          => TransactionStatus::Completed,
                    'idempotency_key' => 'seller-withdraw-'.$withdraw->id,
                    'metadata'        => ['seller_id' => $seller->id],
                    'created_at'      => $processedAt ?? $createdAt,
                    'updated_at'      => $processedAt ?? $createdAt,
                ]);
            }
        }
    }

    protected function seedPlatformPayouts(): void
    {
        $eligibleItems = OrderItem::query()
            ->with('order')
            ->where('status', OrderStatus::Completed)
            ->whereNotNull('completed_at')
            ->where('completed_at', '<=', now()->subDays(7))
            ->whereDoesntHave('platformPayoutItem')
            ->orderBy('completed_at')
            ->get()
            ->filter(fn (OrderItem $orderItem): bool => $this->platformProfitAmount($orderItem) > 0)
            ->values();

        if ($eligibleItems->isEmpty()) {
            return;
        }

        $groups = $eligibleItems
            ->groupBy(fn (OrderItem $orderItem): string => Carbon::parse($orderItem->completed_at)->startOfWeek()->toDateString())
            ->sortKeys()
            ->values();

        $recentGroups = $groups->slice(max(0, $groups->count() - 8));

        foreach ($recentGroups as $group) {
            /** @var Collection<int, OrderItem> $group */
            $firstItem = $group->first();

            if (! $firstItem instanceof OrderItem || $firstItem->completed_at === null) {
                continue;
            }

            $periodStart = Carbon::parse($firstItem->completed_at)->startOfWeek();
            $periodEnd = $periodStart->copy()->endOfWeek();
            $processedAt = $periodEnd->copy()->addDays(7)->setTime(2, 0, 0);

            if ($processedAt->greaterThan(now())) {
                continue;
            }

            $amount = round($group->sum(fn (OrderItem $orderItem): float => $this->platformProfitAmount($orderItem)), 2);

            if ($amount <= 0) {
                continue;
            }

            $idempotencyKey = 'seed-platform-payout-'.$periodStart->format('Ymd');
            $payoutCode = 'PPO-SEED-'.$periodStart->format('Ymd');

            if (PlatformPayout::query()
                ->where(function ($query) use ($idempotencyKey, $payoutCode): void {
                    $query->where('idempotency_key', $idempotencyKey)
                        ->orWhere('payout_code', $payoutCode);
                })
                ->exists()
            ) {
                continue;
            }

            $platformPayout = PlatformPayout::create([
                'payout_code'          => $payoutCode,
                'period_start'         => $periodStart->toDateString(),
                'period_end'           => $periodEnd->toDateString(),
                'settlement_cutoff_at' => $periodEnd,
                'amount'               => $amount,
                'status'               => PlatformPayoutStatus::Completed,
                'bank_name'            => 'Vietcombank',
                'bank_code'            => 'VCB',
                'bank_account_number'  => '0123456789',
                'bank_account_name'    => 'KEYCOVE OWNER',
                'processed_at'         => $processedAt,
                'idempotency_key'      => $idempotencyKey,
                'metadata'             => [
                    'seeded'                    => true,
                    'eligible_order_item_count' => $group->count(),
                    'settlement_days'           => 7,
                ],
                'created_at' => $processedAt->copy()->subMinutes(random_int(20, 180)),
                'updated_at' => $processedAt,
            ]);

            foreach ($group as $orderItem) {
                $platformPayout->items()->create([
                    'order_item_id' => $orderItem->id,
                    'amount'        => $this->platformProfitAmount($orderItem),
                    'profit_type'   => $orderItem->seller_id === null ? 'platform_owned_sale' : 'platform_fee',
                    'metadata'      => [
                        'seller_id'    => $orderItem->seller_id,
                        'order_id'     => $orderItem->order_id,
                        'completed_at' => $orderItem->completed_at?->toDateTimeString(),
                        'seeded'       => true,
                    ],
                    'created_at' => $processedAt,
                    'updated_at' => $processedAt,
                ]);
            }

            $this->createPlatformPayoutLedger($platformPayout, $processedAt);
            $this->internalWalletBalance -= $amount;
        }
    }

    protected function createPlatformPayoutLedger(PlatformPayout $platformPayout, Carbon $processedAt): void
    {
        InternalWalletEntry::create([
            'wallet_id'       => $this->internalWallet->id,
            'order_id'        => null,
            'source_type'     => PlatformPayout::class,
            'source_id'       => $platformPayout->id,
            'type'            => InternalWalletEntryType::PlatformProfitPayoutRequested,
            'direction'       => InternalWalletDirection::Outflow,
            'amount'          => $platformPayout->amount,
            'status'          => TransactionStatus::Completed,
            'affects_balance' => false,
            'idempotency_key' => 'seed-platform-payout-requested-'.$platformPayout->id,
            'metadata'        => [
                'payout_code' => $platformPayout->payout_code,
                'seeded'      => true,
            ],
            'occurred_at' => $platformPayout->created_at ?? $processedAt->copy()->subHour(),
            'created_at'  => $platformPayout->created_at ?? $processedAt->copy()->subHour(),
            'updated_at'  => $platformPayout->created_at ?? $processedAt->copy()->subHour(),
        ]);

        InternalWalletEntry::create([
            'wallet_id'       => $this->internalWallet->id,
            'order_id'        => null,
            'source_type'     => PlatformPayout::class,
            'source_id'       => $platformPayout->id,
            'type'            => InternalWalletEntryType::PlatformProfitPayoutCompleted,
            'direction'       => InternalWalletDirection::Outflow,
            'amount'          => $platformPayout->amount,
            'status'          => TransactionStatus::Completed,
            'affects_balance' => true,
            'idempotency_key' => 'seed-platform-payout-completed-'.$platformPayout->id,
            'metadata'        => [
                'payout_code'     => $platformPayout->payout_code,
                'gateway_message' => 'Seeded completed payout',
                'seeded'          => true,
            ],
            'occurred_at' => $processedAt,
            'created_at'  => $processedAt,
            'updated_at'  => $processedAt,
        ]);
    }

    protected function syncDerivedBalances(): void
    {
        foreach ($this->walletTotals as $walletId => $totals) {
            Wallet::query()->whereKey($walletId)->update([
                'balance'    => round($totals['balance'], 2),
                'holding'    => round($totals['holding'], 2),
                'updated_at' => now(),
            ]);
        }

        $this->internalWallet->update([
            'balance' => round($this->internalWalletBalance, 2),
            'holding' => 0,
        ]);

        ProductListing::query()->get()->each(function (ProductListing $listing): void {
            $listing->updateQuietly([
                'stock_count' => $listing->keys()->where('status', ProductKeyStatus::Available)->count(),
            ]);
        });
    }

    protected function makeOrderTimestamp(int $index): Carbon
    {
        $daysOffset = (int) round(180 - (($index / max(1, self::ORDER_COUNT - 1)) * 179));
        $weekendBoost = fake()->boolean(28) ? random_int(0, 2) : 0;

        return now()
            ->subDays(max(0, $daysOffset - $weekendBoost))
            ->setTime(random_int(8, 23), random_int(0, 59), random_int(0, 59));
    }

    protected function resolveOrderStatus(Carbon $createdAt): OrderStatus
    {
        $ageInDays = $createdAt->diffInDays(now());

        if ($ageInDays <= 1) {
            return fake()->randomElement([
                OrderStatus::PendingPayment,
                OrderStatus::PendingPayment,
                OrderStatus::Processing,
                OrderStatus::Delivered,
            ]);
        }

        if ($ageInDays <= 7) {
            return fake()->randomElement([
                OrderStatus::Processing,
                OrderStatus::Delivered,
                OrderStatus::Completed,
                OrderStatus::Cancelled,
            ]);
        }

        return fake()->randomElement([
            OrderStatus::Completed,
            OrderStatus::Completed,
            OrderStatus::Completed,
            OrderStatus::Completed,
            OrderStatus::Delivered,
            OrderStatus::Processing,
            OrderStatus::Refunded,
            OrderStatus::Disputing,
            OrderStatus::Cancelled,
        ]);
    }

    protected function resolvePaymentStatus(OrderStatus $status): PaymentStatus
    {
        return match ($status) {
            OrderStatus::PendingPayment => PaymentStatus::Pending,
            OrderStatus::Cancelled      => PaymentStatus::Failed,
            OrderStatus::Refunded       => PaymentStatus::Refunded,
            default                     => PaymentStatus::Completed,
        };
    }

    protected function resolveOrderUpdatedAt(Carbon $createdAt, OrderStatus $status): Carbon
    {
        $deltaHours = match ($status) {
            OrderStatus::PendingPayment => random_int(1, 6),
            OrderStatus::Processing     => random_int(2, 18),
            OrderStatus::Delivered      => random_int(4, 36),
            OrderStatus::Completed      => random_int(24, 120),
            OrderStatus::Disputing      => random_int(24, 96),
            OrderStatus::Refunded       => random_int(24, 120),
            OrderStatus::Cancelled      => random_int(1, 12),
        };

        return $createdAt->copy()->addHours($deltaHours);
    }

    protected function resolveQuantity(ProductListing $listing): int
    {
        $stock = $this->stockLevels[$listing->id] ?? 0;
        $isGiftCard = str_contains(strtolower($listing->variant->product->name), 'card')
            || str_contains((string) $listing->variant->edition, '$');
        $desiredQuantity = $isGiftCard
            ? fake()->randomElement([1, 1, 2, 2, 3])
            : fake()->randomElement([1, 1, 1, 1, 2]);

        return max(1, min($stock, $desiredQuantity));
    }

    protected function resolveBuyerKeyViewedAt(Carbon $createdAt, OrderStatus $status): ?Carbon
    {
        if (! in_array($status, [OrderStatus::Delivered, OrderStatus::Completed, OrderStatus::Disputing, OrderStatus::Refunded], true)) {
            return null;
        }

        return $createdAt->copy()->addHours(random_int(1, 48));
    }

    protected function resolveDeliveredAt(Carbon $createdAt, OrderStatus $status): ?Carbon
    {
        if (! in_array($status, [OrderStatus::Delivered, OrderStatus::Completed, OrderStatus::Disputing, OrderStatus::Refunded], true)) {
            return null;
        }

        return $createdAt->copy()->addHours(random_int(4, 36));
    }

    protected function resolveCompletedAt(Carbon $createdAt, OrderStatus $status): ?Carbon
    {
        if ($status !== OrderStatus::Completed) {
            return null;
        }

        return $createdAt->copy()->addHours(random_int(48, 144));
    }

    protected function platformProfitAmount(OrderItem $orderItem): float
    {
        if ($orderItem->seller_id === null) {
            return round((float) $orderItem->seller_amount, 2);
        }

        return round((float) $orderItem->platform_fee, 2);
    }

    protected function statusConsumesInventory(OrderStatus $status): bool
    {
        return in_array($status, [OrderStatus::Delivered, OrderStatus::Completed, OrderStatus::Disputing, OrderStatus::Refunded], true);
    }

    protected function makeOrderCode(Carbon $createdAt): string
    {
        return 'ORD-'.$createdAt->format('Ymd').'-'.strtoupper(Str::random(6));
    }

    protected function makeOrderItemCode(Carbon $createdAt): string
    {
        return 'ITEM-'.$createdAt->format('ymd').'-'.strtoupper(Str::random(7));
    }

    protected function randomBankCode(): string
    {
        return fake()->randomElement(['VCB', 'TCB', 'BIDV', 'ACB', 'MB', 'VNPAYQR']);
    }

    protected function responseCodeForPaymentStatus(PaymentStatus $paymentStatus): string
    {
        return match ($paymentStatus) {
            PaymentStatus::Completed => '00',
            PaymentStatus::Refunded  => '00',
            PaymentStatus::Pending   => '91',
            PaymentStatus::Cancelled => '24',
            PaymentStatus::Failed    => fake()->randomElement(['07', '09', '11', '65']),
        };
    }
}
