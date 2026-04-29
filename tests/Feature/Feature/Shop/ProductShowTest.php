<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Livewire\Shop\Product\ProductShow;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\User;
use Livewire\Livewire;

it('renders the detail page for a scoped admin listing', function (): void {
    $category = Category::factory()->create(['status' => GeneralStatus::Active]);
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $product = Product::factory()->withCategories($category)->create([
        'name'   => 'Detail Product',
        'slug'   => 'detail-product',
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

    $listing = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Detail Bundle',
        'price'        => 123456,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);

    $response = $this->get(route('app.products.show', ['product' => $product->slug, 'listing' => $listing->slug]));

    $response->assertOk();
    $response->assertSeeLivewire(ProductShow::class);
    $response->assertSee('Detail Bundle');
});

it('allows signed in users to add the detail listing to cart', function (): void {
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'name'   => 'Detail Cart Product',
        'slug'   => 'detail-cart-product',
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

    $listing = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Detail Cart Bundle',
        'price'        => 223000,
        'stock_count'  => 3,
        'status'       => ProductListingStatus::Active,
    ]);

    Livewire::actingAs($user)
        ->test(ProductShow::class, ['product' => $product, 'listing' => $listing])
        ->call('addToCart', $listing->id)
        ->assertDispatched('shop:cart:add');

    expect(CartItem::query()->where('listing_id', $listing->id)->count())->toBe(1);
});

it('returns 404 when product and listing do not match', function (): void {
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $productA = Product::factory()->create(['slug' => 'product-a', 'status' => GeneralStatus::Active]);
    $productB = Product::factory()->create(['slug' => 'product-b', 'status' => GeneralStatus::Active]);

    $variant = ProductVariant::factory()->create([
        'product_id'  => $productA->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
    ]);

    $listing = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Mismatched Listing',
        'price'        => 111111,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);

    $this->get(route('app.products.show', ['product' => $productB->slug, 'listing' => $listing->slug]))
        ->assertNotFound();
});

it('generates unique listing slugs automatically', function (): void {
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);
    $product = Product::factory()->create(['name' => 'Slug Product', 'slug' => 'slug-product', 'status' => GeneralStatus::Active]);

    $variant = ProductVariant::factory()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
        'edition'     => 'Standard Edition',
    ]);

    $first = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Same Title',
        'price'        => 100000,
        'stock_count'  => 1,
        'status'       => ProductListingStatus::Active,
    ]);

    $second = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Same Title',
        'price'        => 200000,
        'stock_count'  => 1,
        'status'       => ProductListingStatus::Active,
    ]);

    expect($first->slug)->not->toBeEmpty()
        ->and($second->slug)->not->toBeEmpty()
        ->and($first->slug)->not->toBe($second->slug);
});
