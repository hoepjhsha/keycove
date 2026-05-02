<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\User;

it('updates a cart item quantity for the signed in owner', function (): void {
    $user = User::factory()->create();
    $listing = createActiveCartListing(['stock_count' => 3]);
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $cartItem = CartItem::factory()->forCart($cart)->withListing($listing)->create(['quantity' => 1]);

    $this->actingAs($user)
        ->patchJson(route('app.cart.items.update', $cartItem), ['quantity' => 8])
        ->assertSuccessful()
        ->assertJsonPath('item.quantity', 3)
        ->assertJsonPath('item.listing_id', $listing->id);

    expect($cartItem->fresh()->quantity)->toBe(3);
});

it('removes a cart item for the signed in owner', function (): void {
    $user = User::factory()->create();
    $listing = createActiveCartListing();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $cartItem = CartItem::factory()->forCart($cart)->withListing($listing)->create(['quantity' => 2]);

    $this->actingAs($user)
        ->deleteJson(route('app.cart.items.destroy', $cartItem))
        ->assertSuccessful()
        ->assertJsonPath('item_id', $cartItem->id)
        ->assertJsonPath('count', 0);

    expect(CartItem::query()->whereKey($cartItem->id)->exists())->toBeFalse();
});

it('returns not found when updating another users cart item', function (): void {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $listing = createActiveCartListing();
    $cart = Cart::factory()->create(['user_id' => $owner->id]);
    $cartItem = CartItem::factory()->forCart($cart)->withListing($listing)->create(['quantity' => 1]);

    $this->actingAs($intruder)
        ->patchJson(route('app.cart.items.update', $cartItem), ['quantity' => 2])
        ->assertNotFound();
});

function createActiveCartListing(array $listingOverrides = []): ProductListing
{
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);
    $product = Product::factory()->create(['status' => GeneralStatus::Active]);

    $variant = ProductVariant::factory()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
        'edition'     => 'Standard Edition',
    ]);

    return ProductListing::factory()->create(array_merge([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Cart Test Listing',
        'price'        => 189000,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ], $listingOverrides));
}
