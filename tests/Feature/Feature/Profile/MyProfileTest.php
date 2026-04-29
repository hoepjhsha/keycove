<?php

declare(strict_types=1);

use App\Enums\Gender;
use App\Livewire\Profile\MyProfile;
use App\Models\User;
use App\Models\UserProfile;

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
});

test('guest cannot access the my profile page', function (): void {
    $response = $this->get('/my-profile');

    $response->assertNotFound();
});
