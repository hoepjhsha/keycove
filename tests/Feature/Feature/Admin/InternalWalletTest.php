<?php

declare(strict_types=1);

use App\Enums\ComplaintStatus;
use App\Enums\EscrowStatus;
use App\Enums\GeneralStatus;
use App\Enums\InternalWalletEntryType;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PlatformPayoutStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Enums\WithdrawStatus;
use App\Livewire\Admin\Action\InternalWallet\InternalWalletIndex;
use App\Livewire\Admin\Action\Withdraw\WithdrawalRequestIndex;
use App\Livewire\Shop\Seller\Withdrawals;
use App\Managers\PaymentManager;
use App\Models\Complaint;
use App\Models\Escrow;
use App\Models\InternalWalletEntry;
use App\Models\OperatingSystem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Platform;
use App\Models\PlatformPayout;
use App\Models\PlatformPayoutItem;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\SystemConfig;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\InternalWalletService;
use App\Services\Payment\VNPayGateway;
use App\Services\Shop\ComplaintService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('admin can access the internal wallet page', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(InternalWalletIndex::class)
        ->assertOk()
        ->assertSee('Ví nội bộ');
});

it('records payment receipts and escrow holds for settled orders', function (): void {
    $buyer = User::factory()->create();
    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $listing = activeListing($seller);

    $order = Order::factory()->forBuyer($buyer)->create([
        'payment_method' => PaymentMethod::VNPay,
        'payment_status' => PaymentStatus::Pending,
        'total_price'    => 199000,
    ]);

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'order_item_code'       => 'OI-20260501-LEDGER1',
        'product_name_snapshot' => 'Internal Wallet Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 19900,
        'seller_amount'         => 179100,
        'status'                => OrderStatus::PendingPayment,
    ]);

    ProductKey::factory()->withListing($listing)->reserved()->create([
        'order_item_id' => $orderItem->id,
    ]);

    $escrow = Escrow::query()->create([
        'order_item_id' => $orderItem->id,
        'seller_id'     => $seller->id,
        'amount'        => 179100,
        'release_date'  => now()->addDays(3),
        'status'        => EscrowStatus::Holding,
    ]);

    PaymentTransaction::factory()->create([
        'order_id'               => $order->id,
        'gateway'                => PaymentMethod::VNPay,
        'gateway_transaction_id' => $order->order_code,
        'status'                 => PaymentStatus::Pending,
        'amount'                 => 199000,
    ]);

    $request = [
        'vnp_TxnRef'       => $order->order_code,
        'vnp_Amount'       => 19900000,
        'vnp_ResponseCode' => '00',
        'vnp_SecureHash'   => hash_hmac('sha512', http_build_query([
            'vnp_Amount'       => 19900000,
            'vnp_ResponseCode' => '00',
            'vnp_TxnRef'       => $order->order_code,
        ]), config('services.payment.vnpay.hash_secret', '')),
    ];

    app(VNPayGateway::class)->handleIpn($request);

    $wallet = Wallet::query()->where('type', WalletType::Internal)->first();
    $entries = InternalWalletEntry::query()->orderBy('id')->get();

    expect($wallet?->balance)->toBe('199000.00')
        ->and($entries)->toHaveCount(2)
        ->and($entries->pluck('type')->all())->toBe([
            InternalWalletEntryType::PaymentReceived,
            InternalWalletEntryType::EscrowHeld,
        ])
        ->and($entries->first(fn (InternalWalletEntry $entry): bool => $entry->source_type === Escrow::class && $entry->source_id === $escrow->id)?->affects_balance)->toBeFalse();
});

it('records payment receipts on the internal wallet transaction ledger for platform-owned orders', function (): void {
    $buyer = User::factory()->create();
    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $listing = activeListing($seller);
    $listing->forceFill(['seller_id' => null])->save();

    $order = Order::factory()->forBuyer($buyer)->create([
        'payment_method' => PaymentMethod::VNPay,
        'payment_status' => PaymentStatus::Pending,
        'total_price'    => 199000,
    ]);

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'order_item_code'       => 'OI-20260501-INTERNALSALE',
        'product_name_snapshot' => 'Internal Sale Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 0,
        'seller_amount'         => 199000,
        'status'                => OrderStatus::PendingPayment,
    ]);

    PaymentTransaction::factory()->create([
        'order_id'               => $order->id,
        'gateway'                => PaymentMethod::VNPay,
        'gateway_transaction_id' => $order->order_code,
        'status'                 => PaymentStatus::Pending,
        'amount'                 => 199000,
    ]);

    $request = [
        'vnp_TxnRef'       => $order->order_code,
        'vnp_Amount'       => 19900000,
        'vnp_ResponseCode' => '00',
        'vnp_SecureHash'   => hash_hmac('sha512', http_build_query([
            'vnp_Amount'       => 19900000,
            'vnp_ResponseCode' => '00',
            'vnp_TxnRef'       => $order->order_code,
        ]), config('services.payment.vnpay.hash_secret', '')),
    ];

    app(VNPayGateway::class)->handleIpn($request);

    $internalWallet = Wallet::query()->where('type', WalletType::Internal)->first();
    $paymentTransaction = Transaction::query()
        ->where('wallet_id', $internalWallet?->id)
        ->where('type', TransactionType::PaymentReceived)
        ->latest('id')
        ->first();

    expect($paymentTransaction)->not->toBeNull()
        ->and($paymentTransaction?->amount)->toBe('199000.00')
        ->and($paymentTransaction?->source_type)->toBe(OrderItem::class)
        ->and($paymentTransaction?->source_id)->toBe($orderItem->id)
        ->and($paymentTransaction?->balance_type)->toBe(TransactionBalanceType::Available);
});

it('records refund payouts and reduces the internal wallet balance', function (): void {
    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $admin = User::factory()->admin()->create();
    $listing = activeListing($seller);
    $buyer = User::factory()->create();

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INTWALLET001',
        'balance'   => 199000,
        'holding'   => 0,
    ]);

    $order = Order::factory()->forBuyer($buyer)->create([
        'payment_status' => PaymentStatus::Completed,
        'total_price'    => 199000,
    ]);

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'order_item_code'       => 'OI-20260501-REFUND',
        'product_name_snapshot' => 'Refund Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 19900,
        'seller_amount'         => 179100,
        'status'                => OrderStatus::Disputing,
    ]);

    Escrow::query()->create([
        'order_item_id' => $orderItem->id,
        'seller_id'     => $seller->id,
        'amount'        => 179100,
        'release_date'  => now()->addDay(),
        'status'        => EscrowStatus::Holding,
    ]);

    Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'SELLERWALLET1',
        'balance'   => 0,
        'holding'   => 179100,
    ]);

    $complaint = Complaint::query()->create([
        'order_item_id'   => $orderItem->id,
        'complaint_code'  => 'CMP-REFUND-1',
        'reason'          => 'Broken key',
        'evidence'        => [],
        'status'          => ComplaintStatus::Open,
        'resolution_note' => null,
        'resolved_by'     => null,
        'resolved_at'     => null,
    ]);

    app(ComplaintService::class)->resolveRefund($complaint, $admin, 'Refund approved');

    $wallet = Wallet::query()->where('type', WalletType::Internal)->first();
    $entry = InternalWalletEntry::query()
        ->where('source_type', Complaint::class)
        ->where('source_id', $complaint->id)
        ->first();

    expect($wallet?->balance)->toBe('0.00')
        ->and($entry)->not->toBeNull()
        ->and($entry?->amount)->toBe('199000.00')
        ->and($entry?->status)->toBe(TransactionStatus::Completed);
});

it('refunds seller-owned complaints through vnpay before recording refund transactions', function (): void {
    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.refund_mock', true);

    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $admin = User::factory()->admin()->create();
    $listing = activeListing($seller);
    $buyer = User::factory()->create();

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INTWALLET005',
        'balance'   => 199000,
        'holding'   => 0,
    ]);

    $order = Order::factory()->forBuyer($buyer)->create([
        'payment_method' => PaymentMethod::VNPay,
        'payment_status' => PaymentStatus::Completed,
        'total_price'    => 199000,
    ]);

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'order_item_code'       => 'OI-20260501-GATEWAYREFUND',
        'product_name_snapshot' => 'Gateway Refund Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 19900,
        'seller_amount'         => 179100,
        'status'                => OrderStatus::Disputing,
    ]);

    $escrow = Escrow::query()->create([
        'order_item_id' => $orderItem->id,
        'seller_id'     => $seller->id,
        'amount'        => 179100,
        'release_date'  => now()->addDay(),
        'status'        => EscrowStatus::Holding,
    ]);

    Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'SELLERWALLET5',
        'balance'   => 0,
        'holding'   => 179100,
    ]);

    PaymentTransaction::factory()->completed()->create([
        'order_id'               => $order->id,
        'gateway'                => PaymentMethod::VNPay,
        'gateway_transaction_id' => $order->order_code,
        'amount'                 => 199000,
        'response_payload'       => [
            'vnp_TransactionNo' => '12345678',
            'vnp_PayDate'       => now()->subMinute()->format('YmdHis'),
        ],
    ]);

    $complaint = Complaint::query()->create([
        'order_item_id'   => $orderItem->id,
        'complaint_code'  => 'CMP-REFUND-GATEWAY-1',
        'reason'          => 'Broken key',
        'evidence'        => [],
        'status'          => ComplaintStatus::Open,
        'resolution_note' => null,
        'resolved_by'     => null,
        'resolved_at'     => null,
    ]);

    app(ComplaintService::class)->refundComplaint($complaint, $admin, 'Refund approved');

    $sellerWallet = Wallet::query()->where('seller_id', $seller->id)->first();
    $refundTransaction = Transaction::query()
        ->where('wallet_id', $sellerWallet?->id)
        ->where('type', TransactionType::Refund)
        ->latest('id')
        ->first();

    expect($complaint->fresh()?->status)->toBe(ComplaintStatus::ApprovedRefund)
        ->and($order->fresh()?->payment_status)->toBe(PaymentStatus::Refunded)
        ->and($escrow->fresh()?->status)->toBe(EscrowStatus::Refunded)
        ->and($sellerWallet?->holding)->toBe('0.00')
        ->and($refundTransaction)->not->toBeNull()
        ->and(data_get($refundTransaction?->payment_info, 'gateway_refund.success'))->toBeTrue()
        ->and($order->paymentTransactions()->latest('id')->first()?->status)->toBe(PaymentStatus::Refunded)
        ->and(data_get($order->paymentTransactions()->latest('id')->first()?->response_payload, 'refund.success'))->toBeTrue();
});

it('refunds platform-owned complaints through vnpay and records an internal refund transaction', function (): void {
    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.refund_mock', true);

    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $admin = User::factory()->admin()->create();
    $listing = activeListing($seller);
    $listing->forceFill(['seller_id' => null])->save();
    $buyer = User::factory()->create();

    $internalWallet = Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INTWALLET006',
        'balance'   => 199000,
        'holding'   => 0,
    ]);

    $order = Order::factory()->forBuyer($buyer)->create([
        'payment_method' => PaymentMethod::VNPay,
        'payment_status' => PaymentStatus::Completed,
        'total_price'    => 199000,
    ]);

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'order_item_code'       => 'OI-20260501-INTERNALREFUND',
        'product_name_snapshot' => 'Internal Refund Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 0,
        'seller_amount'         => 199000,
        'status'                => OrderStatus::Disputing,
    ]);

    PaymentTransaction::factory()->completed()->create([
        'order_id'               => $order->id,
        'gateway'                => PaymentMethod::VNPay,
        'gateway_transaction_id' => $order->order_code,
        'amount'                 => 199000,
        'response_payload'       => [
            'vnp_TransactionNo' => '87654321',
            'vnp_PayDate'       => now()->subMinute()->format('YmdHis'),
        ],
    ]);

    $complaint = Complaint::query()->create([
        'order_item_id'   => $orderItem->id,
        'complaint_code'  => 'CMP-REFUND-INTERNAL-1',
        'reason'          => 'Wrong item',
        'evidence'        => [],
        'status'          => ComplaintStatus::Open,
        'resolution_note' => null,
        'resolved_by'     => null,
        'resolved_at'     => null,
    ]);

    app(ComplaintService::class)->refundComplaint($complaint, $admin, 'Refund approved');

    $refundTransaction = Transaction::query()
        ->where('wallet_id', $internalWallet->id)
        ->where('type', TransactionType::Refund)
        ->latest('id')
        ->first();

    expect($complaint->fresh()?->status)->toBe(ComplaintStatus::ApprovedRefund)
        ->and($order->fresh()?->payment_status)->toBe(PaymentStatus::Refunded)
        ->and($internalWallet->fresh()?->balance)->toBe('0.00')
        ->and($refundTransaction)->not->toBeNull()
        ->and($refundTransaction?->amount)->toBe('-199000.00')
        ->and($order->paymentTransactions()->latest('id')->first()?->status)->toBe(PaymentStatus::Refunded)
        ->and(data_get($refundTransaction?->payment_info, 'gateway_refund.success'))->toBeTrue();
});

it('rolls back complaint refund changes when the refund ledger write fails', function (): void {
    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $admin = User::factory()->admin()->create();
    $listing = activeListing($seller);
    $buyer = User::factory()->create();

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INTWALLET004',
        'balance'   => 199000,
        'holding'   => 0,
    ]);

    $order = Order::factory()->forBuyer($buyer)->create([
        'payment_status' => PaymentStatus::Completed,
        'total_price'    => 199000,
    ]);

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'order_item_code'       => 'OI-20260501-ROLLBACK',
        'product_name_snapshot' => 'Refund Rollback Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 19900,
        'seller_amount'         => 179100,
        'status'                => OrderStatus::Disputing,
    ]);

    $escrow = Escrow::query()->create([
        'order_item_id' => $orderItem->id,
        'seller_id'     => $seller->id,
        'amount'        => 179100,
        'release_date'  => now()->addDay(),
        'status'        => EscrowStatus::Holding,
    ]);

    Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'SELLERWALLET4',
        'balance'   => 0,
        'holding'   => 179100,
    ]);

    $complaint = Complaint::query()->create([
        'order_item_id'   => $orderItem->id,
        'complaint_code'  => 'CMP-REFUND-ROLLBACK',
        'reason'          => 'Broken key',
        'evidence'        => [],
        'status'          => ComplaintStatus::Open,
        'resolution_note' => null,
        'resolved_by'     => null,
        'resolved_at'     => null,
    ]);

    $failingComplaintService = new ComplaintService(
        new class extends InternalWalletService
        {
            public function refundPaid(Complaint $complaint, ?Carbon $occurredAt = null): InternalWalletEntry
            {
                throw new RuntimeException('Ledger write failed.');
            }
        },
        app(PaymentManager::class),
    );

    expect(fn () => $failingComplaintService->resolveRefund($complaint, $admin, 'Refund approved'))
        ->toThrow(RuntimeException::class, 'Ledger write failed.');

    expect($complaint->fresh()?->status)->toBe(ComplaintStatus::Open)
        ->and($complaint->fresh()?->resolved_by)->toBeNull()
        ->and($complaint->fresh()?->resolved_at)->toBeNull()
        ->and($orderItem->fresh()?->status)->toBe(OrderStatus::Disputing)
        ->and($escrow->fresh()?->status)->toBe(EscrowStatus::Holding)
        ->and(Wallet::query()->where('seller_id', $seller->id)->first()?->holding)->toBe('179100.00')
        ->and(Wallet::query()->where('seller_id', $seller->id)->first()?->balance)->toBe('0.00')
        ->and(InternalWalletEntry::query()->count())->toBe(0);
});

it('records seller payout requests as pending against the internal wallet', function (): void {
    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.withdraw_mock', true);

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INTWALLET002',
        'balance'   => 25000,
        'holding'   => 0,
    ]);

    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $sellerWallet = Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'SELLERWALLET2',
        'balance'   => 12000,
        'holding'   => 2000,
    ]);

    Livewire::actingAs($sellerUser)
        ->test(Withdrawals::class)
        ->set('amount', '10000')
        ->set('bankName', 'Vietcombank')
        ->set('bankCode', 'VCB')
        ->set('bankAccountNumber', '0123456789')
        ->set('bankAccountName', 'Nguyen Van A')
        ->call('submit')
        ->assertHasNoErrors();

    $internalWallet = Wallet::query()->where('type', WalletType::Internal)->first();
    $entries = InternalWalletEntry::query()->orderBy('id')->get();

    expect($sellerWallet->fresh()->balance)->toBe('2000.00')
        ->and($sellerWallet->fresh()->holding)->toBe('12000.00')
        ->and($internalWallet?->balance)->toBe('25000.00')
        ->and($entries)->toHaveCount(1)
        ->and($entries->pluck('type')->all())->toBe([
            InternalWalletEntryType::SellerPayoutRequested,
        ])
        ->and($entries->pluck('status')->all())->toBe([
            TransactionStatus::Pending,
        ]);
});

it('keeps the internal wallet balance unchanged when an approved seller payout fails', function (): void {
    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.withdraw_mock', true);
    config()->set('queue.default', 'sync');

    SystemConfig::query()->updateOrCreate(
        ['key' => 'min_withdrawal_amount'],
        ['value' => '1', 'description' => 'Minimum amount for seller withdrawals']
    );

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INTWALLET003',
        'balance'   => 25000,
        'holding'   => 0,
    ]);

    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);

    $sellerWallet = Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'SELLERWALLET3',
        'balance'   => 12000,
        'holding'   => 2000,
    ]);

    Livewire::actingAs($sellerUser)
        ->test(Withdrawals::class)
        ->set('amount', '9999')
        ->set('bankName', 'Vietcombank')
        ->set('bankCode', 'VCB')
        ->set('bankAccountNumber', '0123456789')
        ->set('bankAccountName', 'Nguyen Van A')
        ->call('submit')
        ->assertHasNoErrors();

    $withdraw = $sellerWallet->withdraws()->firstOrFail();
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(WithdrawalRequestIndex::class)
        ->call('approve', $withdraw->id)
        ->assertHasNoErrors();

    $internalWallet = Wallet::query()->where('type', WalletType::Internal)->first();
    $entries = InternalWalletEntry::query()->orderBy('id')->get();

    expect($internalWallet?->balance)->toBe('25000.00')
        ->and($entries)->toHaveCount(2)
        ->and($withdraw->fresh()->status)->toBe(WithdrawStatus::Failed)
        ->and($sellerWallet->fresh()->balance)->toBe('12000.00')
        ->and($sellerWallet->fresh()->holding)->toBe('2000.00')
        ->and($entries->pluck('type')->all())->toBe([
            InternalWalletEntryType::SellerPayoutRequested,
            InternalWalletEntryType::SellerPayoutFailed,
        ])
        ->and($entries->last()?->status)->toBe(TransactionStatus::Failed);
});

it('backfills internal wallet entries idempotently', function (): void {
    $buyer = User::factory()->create();
    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $listing = activeListing($seller);

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INTWALLET004',
        'balance'   => 0,
        'holding'   => 0,
    ]);

    $order = Order::factory()->forBuyer($buyer)->create([
        'payment_method' => PaymentMethod::VNPay,
        'payment_status' => PaymentStatus::Completed,
        'total_price'    => 199000,
    ]);

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'order_item_code'       => 'OI-20260501-BACKFILL',
        'product_name_snapshot' => 'Backfill Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 19900,
        'seller_amount'         => 179100,
        'status'                => OrderStatus::Refunded,
    ]);

    $escrow = Escrow::query()->create([
        'order_item_id' => $orderItem->id,
        'seller_id'     => $seller->id,
        'amount'        => 179100,
        'release_date'  => now()->addDay(),
        'status'        => EscrowStatus::Refunded,
    ]);

    PaymentTransaction::factory()->completed()->create([
        'order_id'               => $order->id,
        'gateway'                => PaymentMethod::VNPay,
        'gateway_transaction_id' => $order->order_code,
        'amount'                 => 199000,
    ]);

    $complaint = Complaint::query()->create([
        'order_item_id'   => $orderItem->id,
        'complaint_code'  => 'CMP-BACKFILL-1',
        'reason'          => 'Backfill refund',
        'evidence'        => [],
        'status'          => ComplaintStatus::ApprovedRefund,
        'resolution_note' => 'Approved',
        'resolved_by'     => null,
        'resolved_at'     => now(),
    ]);

    $sellerWallet = Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'SELLERWALLET4',
        'balance'   => 1000,
        'holding'   => 0,
    ]);

    $withdraw = $sellerWallet->withdraws()->create([
        'amount'              => 1000,
        'status'              => WithdrawStatus::Completed,
        'bank_name'           => 'Vietcombank',
        'bank_account_number' => '0123456789',
        'bank_account_name'   => 'Nguyen Van A',
        'requested_by'        => $sellerUser->id,
        'processed_at'        => now(),
        'metadata'            => ['bank_code' => 'VCB'],
    ]);

    $this->artisan('app:backfill-internal-wallet')->assertSuccessful();
    $firstCount = InternalWalletEntry::query()->count();
    $firstBalance = Wallet::query()->where('type', WalletType::Internal)->value('balance');

    $this->artisan('app:backfill-internal-wallet')->assertSuccessful();

    expect($escrow)->not->toBeNull()
        ->and($complaint)->not->toBeNull()
        ->and($withdraw)->not->toBeNull()
        ->and(InternalWalletEntry::query()->count())->toBe($firstCount)
        ->and(Wallet::query()->where('type', WalletType::Internal)->value('balance'))->toBe($firstBalance)
        ->and($firstBalance)->toBe('-1000.00');
});

it('auto completes delivered seller items after the response window and releases escrow', function (): void {
    Carbon::setTestNow('2026-05-12 10:00:00');

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INT-AUTOCOMP-001',
        'balance'   => 50000,
        'holding'   => 0,
    ]);

    $buyer = User::factory()->create();
    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $listing = activeListing($seller);

    $order = Order::factory()->forBuyer($buyer)->create([
        'payment_status' => PaymentStatus::Completed,
        'total_price'    => 199000,
    ]);

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'order_item_code'       => 'OI-20260512-AUTOCOMP',
        'product_name_snapshot' => 'Auto Complete Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 19900,
        'seller_amount'         => 179100,
        'status'                => OrderStatus::Delivered,
        'delivered_at'          => now()->subDays(8),
        'buyer_key_viewed_at'   => now()->subDays(8),
    ]);

    $escrow = Escrow::query()->create([
        'order_item_id' => $orderItem->id,
        'seller_id'     => $seller->id,
        'amount'        => 179100,
        'release_date'  => now()->subDay(),
        'status'        => EscrowStatus::Holding,
    ]);

    $sellerWallet = Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'SELLER-AUTOCOMP-001',
        'balance'   => 0,
        'holding'   => 179100,
    ]);

    $this->artisan('app:complete-settled-order-items', ['--days' => 7])->assertSuccessful();

    expect($orderItem->fresh()->status)->toBe(OrderStatus::Completed)
        ->and($orderItem->fresh()->completed_at)->not->toBeNull()
        ->and($escrow->fresh()->status)->toBe(EscrowStatus::Released)
        ->and($sellerWallet->fresh()->balance)->toBe('179100.00')
        ->and($sellerWallet->fresh()->holding)->toBe('0.00')
        ->and(InternalWalletEntry::query()->where('type', InternalWalletEntryType::EscrowReleased)->count())->toBe(1);
});

it('processes weekly platform profit payouts for eligible completed items', function (): void {
    Carbon::setTestNow('2026-05-12 10:00:00');

    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.withdraw_mock', true);

    configurePlatformPayoutSettings();

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INT-PAYOUT-001',
        'balance'   => 50000,
        'holding'   => 0,
    ]);

    $buyer = User::factory()->create();
    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $sellerListing = activeListing($seller);
    $platformListing = activeListing($seller);
    $platformListing->forceFill(['seller_id' => null])->save();

    createCompletedOrderItem($buyer, $sellerListing, [
        'seller_id'     => $seller->id,
        'platform_fee'  => 1900,
        'seller_amount' => 17100,
        'completed_at'  => now()->subDays(8),
        'delivered_at'  => now()->subDays(10),
    ]);

    createCompletedOrderItem($buyer, $platformListing, [
        'seller_id'     => null,
        'platform_fee'  => 0,
        'seller_amount' => 5000,
        'completed_at'  => now()->subDays(9),
        'delivered_at'  => now()->subDays(11),
    ]);

    createCompletedOrderItem($buyer, $platformListing, [
        'seller_id'     => null,
        'platform_fee'  => 0,
        'seller_amount' => 7000,
        'completed_at'  => now()->subDays(3),
        'delivered_at'  => now()->subDays(4),
    ]);

    $this->artisan('app:process-platform-profit-payouts')->assertSuccessful();

    $platformPayout = PlatformPayout::query()->with('items')->first();

    expect($platformPayout)->not->toBeNull()
        ->and($platformPayout?->status)->toBe(PlatformPayoutStatus::Completed)
        ->and($platformPayout?->amount)->toBe('6900.00')
        ->and($platformPayout?->items)->toHaveCount(2)
        ->and(Wallet::query()->where('type', WalletType::Internal)->value('balance'))->toBe('43100.00')
        ->and(InternalWalletEntry::query()->where('type', InternalWalletEntryType::PlatformProfitPayoutRequested)->count())->toBe(1)
        ->and(InternalWalletEntry::query()->where('type', InternalWalletEntryType::PlatformProfitPayoutCompleted)->count())->toBe(1);
});

it('marks weekly platform profit payouts as failed without reducing the internal wallet balance', function (): void {
    Carbon::setTestNow('2026-05-12 10:00:00');

    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.withdraw_mock', true);

    configurePlatformPayoutSettings();

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INT-PAYOUT-FAIL-001',
        'balance'   => 20000,
        'holding'   => 0,
    ]);

    $buyer = User::factory()->create();
    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $listing = activeListing($seller);
    $listing->forceFill(['seller_id' => null])->save();

    createCompletedOrderItem($buyer, $listing, [
        'seller_id'     => null,
        'platform_fee'  => 0,
        'seller_amount' => 9999,
        'completed_at'  => now()->subDays(8),
        'delivered_at'  => now()->subDays(9),
    ]);

    $this->artisan('app:process-platform-profit-payouts')->assertSuccessful();

    $platformPayout = PlatformPayout::query()->first();

    expect($platformPayout)->not->toBeNull()
        ->and($platformPayout?->status)->toBe(PlatformPayoutStatus::Failed)
        ->and(Wallet::query()->where('type', WalletType::Internal)->value('balance'))->toBe('20000.00')
        ->and(InternalWalletEntry::query()->where('type', InternalWalletEntryType::PlatformProfitPayoutRequested)->count())->toBe(1)
        ->and(InternalWalletEntry::query()->where('type', InternalWalletEntryType::PlatformProfitPayoutFailed)->count())->toBe(1)
        ->and(InternalWalletEntry::query()->where('type', InternalWalletEntryType::PlatformProfitPayoutCompleted)->count())->toBe(0);
});

it('keeps weekly platform profit payouts idempotent across repeated runs', function (): void {
    Carbon::setTestNow('2026-05-12 10:00:00');

    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.withdraw_mock', true);

    configurePlatformPayoutSettings();

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INT-PAYOUT-IDEMP-001',
        'balance'   => 30000,
        'holding'   => 0,
    ]);

    $buyer = User::factory()->create();
    $sellerUser = User::factory()->seller()->create();
    $seller = approvedSeller($sellerUser);
    $listing = activeListing($seller);

    createCompletedOrderItem($buyer, $listing, [
        'seller_id'     => $seller->id,
        'platform_fee'  => 1000,
        'seller_amount' => 9000,
        'completed_at'  => now()->subDays(8),
        'delivered_at'  => now()->subDays(9),
    ]);

    $this->artisan('app:process-platform-profit-payouts')->assertSuccessful();
    $this->artisan('app:process-platform-profit-payouts')->assertSuccessful();

    expect(PlatformPayout::query()->count())->toBe(1)
        ->and(PlatformPayoutItem::query()->count())->toBe(1)
        ->and(Wallet::query()->where('type', WalletType::Internal)->value('balance'))->toBe('29000.00')
        ->and(InternalWalletEntry::query()->where('type', InternalWalletEntryType::PlatformProfitPayoutCompleted)->count())->toBe(1);
});

function approvedSeller(User $user): Seller
{
    return Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Shop '.Str::upper(Str::random(4)),
        'cccd_number'         => (string) random_int(100000000000, 999999999999),
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);
}

function activeListing(Seller $seller): ProductListing
{
    $region = Region::factory()->create([
        'status'    => GeneralStatus::Active,
        'slug'      => Str::uuid()->toString(),
        'flag_code' => Str::substr(Str::uuid()->toString(), 0, 10),
    ]);

    $platform = Platform::factory()->create([
        'status' => GeneralStatus::Active,
        'slug'   => Str::uuid()->toString(),
    ]);

    $operatingSystem = OperatingSystem::factory()->create([
        'status' => GeneralStatus::Active,
        'slug'   => Str::uuid()->toString(),
    ]);

    $product = Product::factory()->create([
        'status'                 => GeneralStatus::Active,
        'submitted_by_seller_id' => $seller->id,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $operatingSystem->id,
        'status'      => ProductVariantStatus::Active,
    ]);

    return ProductListing::factory()->withSeller($seller)->create([
        'variant_id'  => $variant->id,
        'price'       => 199000,
        'stock_count' => 5,
        'status'      => ProductListingStatus::Active,
    ]);
}

function configurePlatformPayoutSettings(): void
{
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_enabled'], ['value' => 'true', 'description' => 'Enable platform profit payouts']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_auto_process'], ['value' => 'true', 'description' => 'Auto process platform profit payouts']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_settlement_days'], ['value' => '7', 'description' => 'Settlement days before platform payout']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_bank_name'], ['value' => 'Vietcombank', 'description' => 'Platform payout bank name']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_bank_code'], ['value' => 'VCB', 'description' => 'Platform payout bank code']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_bank_account_number'], ['value' => '0123456789', 'description' => 'Platform payout bank account number']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_bank_account_name'], ['value' => 'KeyCove Owner', 'description' => 'Platform payout bank account holder']);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function createCompletedOrderItem(User $buyer, ProductListing $listing, array $overrides = []): OrderItem
{
    $order = Order::factory()->forBuyer($buyer)->create([
        'payment_status' => PaymentStatus::Completed,
        'total_price'    => (float) ($overrides['subtotal'] ?? 19900),
    ]);

    return $order->items()->create(array_merge([
        'listing_id'            => $listing->id,
        'seller_id'             => $overrides['seller_id'] ?? $listing->seller_id,
        'order_item_code'       => 'OI-'.Str::upper(Str::random(10)),
        'product_name_snapshot' => 'Completed payout item',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 19900,
        'subtotal'              => 19900,
        'platform_fee'          => 1900,
        'seller_amount'         => 18000,
        'status'                => OrderStatus::Completed,
        'delivered_at'          => now()->subDays(10),
        'completed_at'          => now()->subDays(8),
        'buyer_key_viewed_at'   => now()->subDays(10),
    ], $overrides));
}
