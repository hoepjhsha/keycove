<?php

use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Action\SystemConfig\SystemConfigIndex;
use App\Models\Seller;
use App\Models\SystemConfig;
use App\Models\User;
use App\Notifications\CommissionRateChangedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('admin can access the system settings page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin, 'admin')
        ->test(SystemConfigIndex::class)
        ->assertOk();
});

it('does not show free-form create edit or delete actions', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $config = SystemConfig::updateOrCreate([
        'key' => SystemConfig::KEY_COMMISSION_RATE,
    ], [
        'value'       => '10',
        'description' => 'Commission rate',
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(SystemConfigIndex::class)
        ->assertSee($config->key)
        ->assertDontSee(__('admin.system_settings.add_setting'))
        ->assertDontSee(__('admin.common.edit'))
        ->assertDontSee(__('admin.common.delete'));
});

it('can update a managed system config inline value', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $config = SystemConfig::updateOrCreate([
        'key' => 'min_withdrawal_amount',
    ], [
        'value'       => '10000',
        'description' => 'Minimum withdrawal amount',
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(SystemConfigIndex::class)
        ->set('inlineValues.'.$config->id, '20000')
        ->call('saveInlineValue', $config->id);

    expect($config->refresh()->value)->toBe('20000');
});

it('blocks inline updates for unmanaged system configs', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $config = SystemConfig::create([
        'key'         => 'site_name',
        'value'       => 'KeyCove',
        'description' => 'Website name',
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(SystemConfigIndex::class)
        ->set('inlineValues.'.$config->id, 'Updated Name')
        ->call('saveInlineValue', $config->id)
        ->assertHasErrors(['inlineValues.'.$config->id]);

    expect($config->refresh()->value)->toBe('KeyCove');
});

it('notifies approved sellers when commission rate changes', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $approvedSellerUser = User::factory()->seller()->create();
    $pendingSellerUser = User::factory()->seller()->create();
    $config = SystemConfig::updateOrCreate([
        'key' => SystemConfig::KEY_COMMISSION_RATE,
    ], [
        'value'       => '10',
        'description' => 'Commission rate',
    ]);

    Seller::create([
        'user_id'          => $approvedSellerUser->id,
        'shop_name'        => 'Approved Shop',
        'cccd_number'      => '123456789012',
        'cccd_front_image' => null,
        'cccd_back_image'  => null,
        'kyc_status'       => KycStatus::Approved,
    ]);

    Seller::create([
        'user_id'          => $pendingSellerUser->id,
        'shop_name'        => 'Pending Shop',
        'cccd_number'      => '234567890123',
        'cccd_front_image' => null,
        'cccd_back_image'  => null,
        'kyc_status'       => KycStatus::Pending,
    ]);

    Notification::fake();

    Livewire::actingAs($admin, 'admin')
        ->test(SystemConfigIndex::class)
        ->set('inlineValues.'.$config->id, '12.5')
        ->call('saveInlineValue', $config->id);

    Notification::assertSentTo(
        $approvedSellerUser,
        CommissionRateChangedNotification::class,
        fn (CommissionRateChangedNotification $notification): bool => $notification->oldRate === '10'
            && $notification->newRate === '12.5',
    );

    Notification::assertNotSentTo($pendingSellerUser, CommissionRateChangedNotification::class);

    expect($config->refresh()->value)->toBe('12.5');
});

it('does not notify sellers when a non commission setting changes', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $sellerUser = User::factory()->seller()->create();
    $config = SystemConfig::updateOrCreate([
        'key' => 'min_withdrawal_amount',
    ], [
        'value'       => '10000',
        'description' => 'Minimum withdrawal amount',
    ]);

    Seller::create([
        'user_id'          => $sellerUser->id,
        'shop_name'        => 'Approved Shop',
        'cccd_number'      => '123456789012',
        'cccd_front_image' => null,
        'cccd_back_image'  => null,
        'kyc_status'       => KycStatus::Approved,
    ]);

    Notification::fake();

    Livewire::actingAs($admin, 'admin')
        ->test(SystemConfigIndex::class)
        ->set('inlineValues.'.$config->id, '15000')
        ->call('saveInlineValue', $config->id);

    Notification::assertNothingSent();
    expect($config->refresh()->value)->toBe('15000');
});
