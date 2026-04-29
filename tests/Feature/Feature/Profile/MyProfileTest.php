<?php

declare(strict_types=1);

use App\Enums\Gender;
use App\Enums\GeneralStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Livewire\Profile\MyProfile;
use App\Models\OperatingSystem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
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
    $response->assertSee('Orders');
    $response->assertSee('Security');
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

test('authenticated user can reveal purchased keys for their own order item after password confirmation', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create();
    $listing = createAdminListingForProfile();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Premium Game Key',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => $listing->price,
        'subtotal'              => $listing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Delivered,
    ]);

    $productKey = ProductKey::factory()->withListing($listing)->sold()->create([
        'order_item_id' => $orderItem->id,
    ]);

    Livewire::actingAs($user)
        ->test(MyProfile::class)
        ->set('section', 'orders')
        ->call('promptKeyReveal', $orderItem->id)
        ->set('keyAccessPassword', 'password')
        ->call('revealOrderItemKeys')
        ->assertHasNoErrors()
        ->assertSet('keyAccessOrderItemId', null)
        ->assertSee($productKey->key_code);

    expect($orderItem->fresh()->buyer_key_viewed_at)->not->toBeNull();
});

test('previously confirmed order item keys can be reopened without entering password again', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create();
    $listing = createAdminListingForProfile();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Reopenable Game Key',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => $listing->price,
        'subtotal'              => $listing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Completed,
        'buyer_key_viewed_at'   => now(),
    ]);

    $productKey = ProductKey::factory()->withListing($listing)->sold()->create([
        'order_item_id' => $orderItem->id,
    ]);

    Livewire::actingAs($user)
        ->test(MyProfile::class)
        ->set('section', 'orders')
        ->call('promptKeyReveal', $orderItem->id)
        ->assertSet('keyAccessOrderItemId', null)
        ->assertSee($productKey->key_code);
});

test('order items outside delivered disputing and completed cannot open key reveal flow', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create();
    $listing = createAdminListingForProfile();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Processing Game Key',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => $listing->price,
        'subtotal'              => $listing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Processing,
    ]);

    ProductKey::factory()->withListing($listing)->sold()->create([
        'order_item_id' => $orderItem->id,
    ]);

    Livewire::actingAs($user)
        ->test(MyProfile::class)
        ->set('section', 'orders')
        ->call('promptKeyReveal', $orderItem->id)
        ->assertSet('keyAccessOrderItemId', null)
        ->assertDontSee('Confirm password to view your key');
});

test('guest cannot access the my profile page', function (): void {
    $response = $this->get('/my-profile');

    $response->assertNotFound();
});

function createAdminListingForProfile(): ProductListing
{
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $product = Product::factory()->create([
        'name'   => 'Profile Order Product',
        'status' => GeneralStatus::Active,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
        'edition'     => 'Standard Edition',
    ]);

    return ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Profile Order Listing',
        'price'        => 149000,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);
}
