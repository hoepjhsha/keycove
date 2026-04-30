<?php

declare(strict_types=1);

use App\Enums\Gender;
use App\Livewire\Shop\Profile\MyProfile;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
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

    $response = $this->actingAs($user)->get('/my-profile');

    $response->assertOk();
    $response->assertSeeLivewire(MyProfile::class);
    $response->assertSee('Hoep Tran');
    $response->assertSee('0987654321');
    $response->assertSee('Profile bio for storefront display.');
    $response->assertSee('Profile');
    $response->assertSee('Security');
    $response->assertSee('My Library');
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
