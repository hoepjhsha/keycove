<?php

declare(strict_types=1);

use App\Enums\Gender;
use App\Enums\KycStatus;
use App\Livewire\Shop\Profile\MyProfile;
use App\Models\Seller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('authenticated user can access the my profile page', function (): void {
    $user = User::factory()->create([
        'username' => 'keycove_user',
        'email'    => 'profile@example.com',
    ]);

    UserProfile::factory()->forUser($user)->create([
        'first_name'   => 'Hoep',
        'last_name'    => 'Tran',
        'gender'       => Gender::Male,
        'phone_number' => '0987654321',
        'bio'          => 'Profile bio for storefront display.',
    ]);

    Livewire::actingAs($user)
        ->test(MyProfile::class)
        ->assertSee('Hoep Tran')
        ->assertSee('0987654321')
        ->assertSee('Profile bio for storefront display.')
        ->assertSee('Profile')
        ->assertSee('Security')
        ->assertSee('My Library');
});

test('verified users can start seller onboarding from my profile', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MyProfile::class)
        ->assertSee('Become a seller')
        ->assertSee(route('seller.apply'));
});

test('approved sellers can open the seller dashboard from my profile', function (): void {
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

    Livewire::actingAs($user)
        ->test(MyProfile::class)
        ->assertSee('Open dashboard')
        ->assertSee(route('seller.dashboard.index'));
});

test('unverified users are shown the seller onboarding restriction on my profile', function (): void {
    $user = User::factory()->unverified()->create();

    Livewire::actingAs($user)
        ->test(MyProfile::class)
        ->assertSee('Verify your email first to unlock seller onboarding.')
        ->assertSee('You need a verified email to continue.');
});

test('authenticated user can change password from the security section', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MyProfile::class)
        ->set('section', 'security')
        ->set('currentPassword', 'password')
        ->set('newPassword', 'new-password-123')
        ->set('newPasswordConfirmation', 'new-password-123')
        ->call('changePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password-123', (string) $user->fresh()->password))->toBeTrue();
});

test('authenticated user can update profile information from my profile', function (): void {
    Storage::fake(config('filesystems.public_disk'));

    $user = User::factory()->create([
        'username' => 'keycove_user',
    ]);

    UserProfile::factory()->forUser($user)->create([
        'first_name'   => 'Old',
        'last_name'    => 'Name',
        'gender'       => Gender::Male,
        'phone_number' => '0911111111',
        'bio'          => 'Old bio.',
    ]);

    $avatar = UploadedFile::fake()->create('avatar.png', 120, 'image/png');

    Livewire::actingAs($user)
        ->test(MyProfile::class)
        ->set('username', 'updated_user')
        ->set('firstName', 'Updated')
        ->set('lastName', 'Profile')
        ->set('dob', '1995-05-21')
        ->set('gender', Gender::Female->value)
        ->set('phoneNumber', '0988888888')
        ->set('bio', 'Updated profile bio.')
        ->set('avatar', $avatar)
        ->call('saveProfile')
        ->assertHasNoErrors()
        ->assertSee('Your profile has been updated successfully.');

    $freshUser = $user->fresh();
    $freshProfile = $freshUser->profile;

    expect($freshUser->username)->toBe('updated_user');
    expect($freshProfile?->first_name)->toBe('Updated');
    expect($freshProfile?->last_name)->toBe('Profile');
    expect($freshProfile?->dob?->format('Y-m-d'))->toBe('1995-05-21');
    expect($freshProfile?->gender)->toBe(Gender::Female);
    expect($freshProfile?->phone_number)->toBe('0988888888');
    expect($freshProfile?->bio)->toBe('Updated profile bio.');
    expect($freshProfile?->avatar)->not->toBeEmpty();

    Storage::disk(config('filesystems.public_disk'))->assertExists($freshProfile->avatar);
});

test('unverified user can request an email verification link from my profile', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    Livewire::actingAs($user)
        ->test(MyProfile::class)
        ->set('section', 'security')
        ->call('sendVerificationLink')
        ->assertHasNoErrors();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('guest cannot access the my profile page', function (): void {
    $response = $this->get('/my-profile');

    $response->assertNotFound();
});
