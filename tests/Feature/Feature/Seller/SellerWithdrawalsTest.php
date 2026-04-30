<?php

declare(strict_types=1);

use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Enums\WithdrawStatus;
use App\Livewire\Shop\Seller\Dashboard;
use App\Livewire\Shop\Seller\Withdrawals;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductListing;
use App\Models\Seller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdraw;
use Livewire\Livewire;

it('shows shop earnings on the seller dashboard', function (): void {
    $user = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Store',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);
    Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'WALLET123456',
        'balance'   => 50_000,
        'holding'   => 7_000,
    ]);

    $order = Order::factory()->forBuyer(User::factory()->create())->create();
    $listing = ProductListing::factory()->withSeller($seller)->active()->create([
        'price' => 100_000,
    ]);

    OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'product_name_snapshot' => 'Test listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 2,
        'unit_price'            => 100_000,
        'subtotal'              => 200_000,
        'platform_fee'          => 20_000,
        'seller_amount'         => 180_000,
        'status'                => OrderStatus::Delivered,
    ]);

    $this->actingAs($user)
        ->get('/seller/dashboard')
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class)
        ->assertSee('180.000 VND')
        ->assertSee('20.000 VND');
});

it('shows the seller withdrawals page', function (): void {
    $user = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Store',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'WALLET123457',
        'balance'   => 50_000,
        'holding'   => 0,
    ]);

    $this->actingAs($user)
        ->get('/seller/withdrawals')
        ->assertOk()
        ->assertSeeLivewire(Withdrawals::class)
        ->assertSee('Withdraw your shop balance');
});

it('processes a successful withdrawal request', function (): void {
    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.withdraw_mock', true);

    $user = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Store',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);
    $wallet = Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'WALLET123458',
        'balance'   => 12_000,
        'holding'   => 2_000,
    ]);

    Livewire::actingAs($user)
        ->test(Withdrawals::class)
        ->set('amount', '1000')
        ->set('bankName', 'Vietcombank')
        ->set('bankCode', 'VCB')
        ->set('bankAccountNumber', '0123456789')
        ->set('bankAccountName', 'Nguyen Van A')
        ->call('submit')
        ->assertHasNoErrors();

    $wallet->refresh();

    expect($wallet->balance)->toBe('11000.00');
    expect($wallet->holding)->toBe('2000.00');

    $withdrawal = Withdraw::query()->first();

    expect($withdrawal)->not->toBeNull();
    expect($withdrawal?->status)->toBe(WithdrawStatus::Completed);
    expect($withdrawal?->amount)->toBe('1000.00');

    $transaction = $wallet->transactions()->latest('id')->first();

    expect($transaction?->type)->toBe(TransactionType::Withdraw);
    expect($transaction?->status)->toBe(TransactionStatus::Completed);
    expect($transaction?->amount)->toBe('-1000.00');
});

it('rejects withdrawal requests above available balance', function (): void {
    $user = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Store',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'WALLET123460',
        'balance'   => 12_000,
        'holding'   => 0,
    ]);

    Livewire::actingAs($user)
        ->test(Withdrawals::class)
        ->set('amount', '13000')
        ->set('bankName', 'Vietcombank')
        ->set('bankCode', 'VCB')
        ->set('bankAccountNumber', '0123456789')
        ->set('bankAccountName', 'Nguyen Van A')
        ->call('submit')
        ->assertHasErrors(['amount']);

    expect(Withdraw::query()->count())->toBe(0);
});

it('restores the wallet when withdrawal fails', function (): void {
    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.withdraw_mock', true);

    $user = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Store',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);
    $wallet = Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'WALLET123459',
        'balance'   => 12_000,
        'holding'   => 2_000,
    ]);

    Livewire::actingAs($user)
        ->test(Withdrawals::class)
        ->set('amount', '9999')
        ->set('bankName', 'Vietcombank')
        ->set('bankCode', 'VCB')
        ->set('bankAccountNumber', '0123456789')
        ->set('bankAccountName', 'Nguyen Van A')
        ->call('submit')
        ->assertHasNoErrors();

    $wallet->refresh();

    expect($wallet->balance)->toBe('12000.00');
    expect($wallet->holding)->toBe('2000.00');

    $withdrawal = Withdraw::query()->first();

    expect($withdrawal)->not->toBeNull();
    expect($withdrawal?->status)->toBe(WithdrawStatus::Failed);

    $transaction = $wallet->transactions()->latest('id')->first();

    expect($transaction?->type)->toBe(TransactionType::Withdraw);
    expect($transaction?->status)->toBe(TransactionStatus::Failed);
    expect($transaction?->amount)->toBe('-9999.00');
});
