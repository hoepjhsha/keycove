<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Livewire\Shop\ProductIndex;
use App\Models\Category;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('shop page is accessible and renders the livewire component', function (): void {
    $response = $this->get(route('app.products.index'));

    $response->assertOk();
    $response->assertSeeLivewire(ProductIndex::class);
});

test('shop page only shows admin listings and falls back to the product name', function (): void {
    $category = Category::factory()->create(['status' => GeneralStatus::Active]);
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $product = Product::factory()->withCategories($category)->create([
        'name'   => 'Cyberpunk 2077',
        'status' => GeneralStatus::Active,
        'slug'   => Str::slug('Cyberpunk 2077 unique storefront product'),
    ]);

    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'External Seller',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
        'edition'     => 'Ultimate Edition',
    ]);

    ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Night City Bundle',
        'price'        => 199000,
        'stock_count'  => 12,
        'status'       => ProductListingStatus::Active,
    ]);

    ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => $seller->id,
        'display_name' => 'Should not show',
        'price'        => 299000,
        'stock_count'  => 12,
        'status'       => ProductListingStatus::Active,
    ]);

    $response = $this->get(route('app.products.index'));

    $response->assertOk();
    $response->assertSee('Night City Bundle');
    $response->assertSee('Cyberpunk 2077');
    $response->assertDontSee('Should not show');
});

test('shop filters by category and stock state', function (): void {
    $category = Category::factory()->create(['status' => GeneralStatus::Active]);
    $otherCategory = Category::factory()->create(['status' => GeneralStatus::Active]);
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $product = Product::factory()->withCategories($category)->create([
        'name'   => 'Filtered Product',
        'status' => GeneralStatus::Active,
        'slug'   => Str::slug('filtered product storefront'),
    ]);

    $otherProduct = Product::factory()->withCategories($otherCategory)->create([
        'name'   => 'Other Product',
        'status' => GeneralStatus::Active,
        'slug'   => Str::slug('other product storefront'),
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
        'edition'     => 'Standard Edition',
    ]);

    $otherVariant = ProductVariant::factory()->create([
        'product_id'  => $otherProduct->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
        'edition'     => 'Standard Edition',
    ]);

    ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => null,
        'price'        => 150000,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);

    ProductListing::factory()->create([
        'variant_id'   => $otherVariant->id,
        'seller_id'    => null,
        'display_name' => 'Other listing',
        'price'        => 250000,
        'stock_count'  => 8,
        'status'       => ProductListingStatus::Active,
    ]);

    Livewire::test(ProductIndex::class)
        ->set('categoryId', $category->id)
        ->set('inStock', true)
        ->assertSee('Filtered Product')
        ->assertDontSee('Other listing');
});

test('shop sorts by price ascending', function (): void {
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $cheapProduct = Product::factory()->create([
        'name'   => 'Cheap Product',
        'status' => GeneralStatus::Active,
        'slug'   => Str::slug('cheap storefront product'),
    ]);

    $expensiveProduct = Product::factory()->create([
        'name'   => 'Expensive Product',
        'status' => GeneralStatus::Active,
        'slug'   => Str::slug('expensive storefront product'),
    ]);

    $cheapVariant = ProductVariant::factory()->create([
        'product_id'  => $cheapProduct->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
        'edition'     => 'Standard Edition',
    ]);

    $expensiveVariant = ProductVariant::factory()->create([
        'product_id'  => $expensiveProduct->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
        'edition'     => 'Standard Edition',
    ]);

    ProductListing::factory()->create([
        'variant_id'   => $expensiveVariant->id,
        'seller_id'    => null,
        'display_name' => 'Premium listing',
        'price'        => 500000,
        'stock_count'  => 7,
        'status'       => ProductListingStatus::Active,
    ]);

    ProductListing::factory()->create([
        'variant_id'   => $cheapVariant->id,
        'seller_id'    => null,
        'display_name' => 'Budget listing',
        'price'        => 100000,
        'stock_count'  => 7,
        'status'       => ProductListingStatus::Active,
    ]);

    Livewire::test(ProductIndex::class)
        ->set('sortBy', 'price_asc')
        ->assertSeeInOrder(['Budget listing', 'Premium listing']);
});
