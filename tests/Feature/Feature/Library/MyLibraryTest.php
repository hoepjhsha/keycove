<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Livewire\Shop\Library\MyLibrary;
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
use Livewire\Livewire;

test('authenticated user can access the my library page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/my-library');

    $response->assertOk();
    $response->assertSeeLivewire(MyLibrary::class);
    $response->assertSee('My Library');
});

test('legacy orders route redirects to my library', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/orders')
        ->assertRedirect('/my-library');
});

test('authenticated user can reveal purchased keys from my library', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create();
    $listing = createAdminListingForLibrary();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Library Game Key',
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
        ->test(MyLibrary::class)
        ->call('promptKeyReveal', $orderItem->id)
        ->set('keyAccessPassword', 'password')
        ->call('revealOrderItemKeys')
        ->assertHasNoErrors()
        ->assertSet('keyAccessOrderItemId', null)
        ->assertSee($productKey->key_code);
});

function createAdminListingForLibrary(): ProductListing
{
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $product = Product::factory()->create([
        'name'   => 'Library Product',
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
        'display_name' => 'Library Listing',
        'price'        => 149000,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);
}
