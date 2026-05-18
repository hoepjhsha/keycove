<?php

declare(strict_types=1);

use App\Enums\PlatformPayoutStatus;
use App\Enums\UserRole;
use App\Enums\WalletType;
use App\Livewire\Admin\Action\PlatformPayout\PlatformPayoutIndex;
use App\Models\PlatformPayout;
use App\Models\SystemConfig;
use App\Models\User;
use App\Models\Wallet;
use Livewire\Livewire;

it('admin can access the platform payouts page', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin, 'admin')
        ->test(PlatformPayoutIndex::class)
        ->assertOk()
        ->assertSee('Payout lợi nhuận');
});

it('admin can view platform payout details', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $platformPayout = platformPayoutFixture();

    Livewire::actingAs($admin, 'admin')
        ->test(PlatformPayoutIndex::class)
        ->call('viewPayout', $platformPayout->id)
        ->assertSet('showViewModal', true)
        ->assertSee($platformPayout->payout_code)
        ->assertSee('Vietcombank');
});

it('admin can process a pending platform payout', function (): void {
    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.withdraw_mock', true);

    configurePlatformPayoutUiSettings();

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INT-PL-001',
        'balance'   => 10000,
        'holding'   => 0,
    ]);

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $platformPayout = platformPayoutFixture(['amount' => 2000, 'status' => PlatformPayoutStatus::Pending]);

    Livewire::actingAs($admin, 'admin')
        ->test(PlatformPayoutIndex::class)
        ->call('process', $platformPayout->id)
        ->assertHasNoErrors();

    expect($platformPayout->fresh()->status)->toBe(PlatformPayoutStatus::Completed)
        ->and(Wallet::query()->where('type', WalletType::Internal)->value('balance'))->toBe('8000.00');
});

it('admin can retry a failed platform payout', function (): void {
    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.withdraw_mock', true);

    configurePlatformPayoutUiSettings();

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INT-PL-002',
        'balance'   => 10000,
        'holding'   => 0,
    ]);

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $platformPayout = platformPayoutFixture(['amount' => 2000, 'status' => PlatformPayoutStatus::Failed]);

    Livewire::actingAs($admin, 'admin')
        ->test(PlatformPayoutIndex::class)
        ->call('retry', $platformPayout->id)
        ->assertHasNoErrors();

    expect($platformPayout->fresh()->status)->toBe(PlatformPayoutStatus::Completed)
        ->and(Wallet::query()->where('type', WalletType::Internal)->value('balance'))->toBe('8000.00');
});

it('admin can cancel a pending platform payout', function (): void {
    configurePlatformPayoutUiSettings();

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $platformPayout = platformPayoutFixture(['status' => PlatformPayoutStatus::Pending]);

    Livewire::actingAs($admin, 'admin')
        ->test(PlatformPayoutIndex::class)
        ->call('cancel', $platformPayout->id)
        ->assertHasNoErrors();

    expect($platformPayout->fresh()->status)->toBe(PlatformPayoutStatus::Cancelled);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function platformPayoutFixture(array $overrides = []): PlatformPayout
{
    return PlatformPayout::factory()->create(array_merge([
        'amount'              => 1500,
        'status'              => PlatformPayoutStatus::Pending,
        'bank_name'           => 'Vietcombank',
        'bank_code'           => 'VCB',
        'bank_account_number' => '0123456789',
        'bank_account_name'   => 'KeyCove Owner',
    ], $overrides));
}

function configurePlatformPayoutUiSettings(): void
{
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_enabled'], ['value' => 'true', 'description' => 'Enable platform profit payouts']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_auto_process'], ['value' => 'true', 'description' => 'Auto process platform profit payouts']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_settlement_days'], ['value' => '7', 'description' => 'Settlement days before platform payout']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_bank_name'], ['value' => 'Vietcombank', 'description' => 'Platform payout bank name']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_bank_code'], ['value' => 'VCB', 'description' => 'Platform payout bank code']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_bank_account_number'], ['value' => '0123456789', 'description' => 'Platform payout bank account number']);
    SystemConfig::query()->updateOrCreate(['key' => 'platform_payout_bank_account_name'], ['value' => 'KeyCove Owner', 'description' => 'Platform payout bank account holder']);
}
