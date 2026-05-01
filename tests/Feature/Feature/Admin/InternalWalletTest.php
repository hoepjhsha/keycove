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
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Enums\TransactionStatus;
use App\Enums\WalletType;
use App\Enums\WithdrawStatus;
use App\Livewire\Admin\Action\InternalWallet\InternalWalletIndex;
use App\Livewire\Admin\Action\Withdraw\WithdrawalRequestIndex;
use App\Livewire\Shop\Seller\Withdrawals;
use App\Models\Complaint;
use App\Models\Escrow;
use App\Models\InternalWalletEntry;
use App\Models\OperatingSystem;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Payment\VNPayGateway;
use App\Services\Shop\ComplaintService;
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
        ->set('amount', '1000')
        ->set('bankName', 'Vietcombank')
        ->set('bankCode', 'VCB')
        ->set('bankAccountNumber', '0123456789')
        ->set('bankAccountName', 'Nguyen Van A')
        ->call('submit')
        ->assertHasNoErrors();

    $internalWallet = Wallet::query()->where('type', WalletType::Internal)->first();
    $entries = InternalWalletEntry::query()->orderBy('id')->get();

    expect($sellerWallet->fresh()->balance)->toBe('11000.00')
        ->and($sellerWallet->fresh()->holding)->toBe('3000.00')
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
