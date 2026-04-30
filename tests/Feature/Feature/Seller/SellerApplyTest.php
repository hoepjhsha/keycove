<?php

declare(strict_types=1);

use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Action\SellerKyc\SellerKycIndex;
use App\Livewire\Shop\Seller\Apply;
use App\Livewire\Shop\Seller\Dashboard;
use App\Models\Seller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('verified users can open the seller application page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/seller/apply')
        ->assertOk()
        ->assertSee('Become a seller')
        ->assertSee('Application details');
});

test('unverified users are redirected away from the seller application page', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/seller/apply')
        ->assertRedirect('/my-profile?section=security')
        ->assertSessionHas('profile-status', 'Verify your email first to unlock seller onboarding.');
});

test('verified users can submit a seller application', function (): void {
    Storage::fake(config('filesystems.default'));

    $user = User::factory()->create();

    $frontImage = UploadedFile::fake()->image('front.jpg');
    $backImage = UploadedFile::fake()->image('back.jpg');

    Livewire::actingAs($user)
        ->test(Apply::class)
        ->set('shopName', 'KeyCove Store')
        ->set('cccdNumber', '123456789012')
        ->set('cccdFrontImage', $frontImage)
        ->set('cccdBackImage', $backImage)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSee('Your seller application has been submitted for review.');

    $seller = Seller::query()->where('user_id', $user->id)->first();

    expect($seller)->not->toBeNull();
    expect($seller?->shop_name)->toBe('KeyCove Store');
    expect($seller?->kyc_status)->toBe(KycStatus::Pending);
    expect($user->fresh()->role)->toBe(UserRole::User);
    expect(Wallet::query()->where('seller_id', $seller?->id)->exists())->toBeTrue();
});

test('admin approval promotes the user to seller role', function (): void {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $seller = Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Store',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Pending,
        'kyc_rejected_reason' => null,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(SellerKycIndex::class)
        ->call('processSellerKyc', $seller->id)
        ->set('processForm.kyc_status', KycStatus::Approved->value)
        ->call('processSellerKycSubmit');

    expect($seller->fresh()->kyc_status)->toBe(KycStatus::Approved);
    expect($user->fresh()->role)->toBe(UserRole::Seller);
});

test('approved sellers can access the seller dashboard', function (): void {
    $user = User::factory()->seller()->create();

    Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Store',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    $this->actingAs($user)
        ->get('/seller/dashboard')
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class)
        ->assertSee('Seller Portal')
        ->assertSee('Recent products')
        ->assertSee('Recent listings');
});

test('pending sellers are redirected from the seller dashboard to the application', function (): void {
    $user = User::factory()->create();

    Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Store',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Pending,
        'kyc_rejected_reason' => null,
    ]);

    $this->actingAs($user)
        ->get('/seller/dashboard')
        ->assertRedirect('/seller/apply')
        ->assertSessionHas('seller-status', 'Complete and submit your seller application first.');
});
