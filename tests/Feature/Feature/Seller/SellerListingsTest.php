<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Table\Product\ProductTable;
use App\Livewire\Shop\Seller\SellerListings;
use App\Livewire\Shop\Seller\Table\SellerListingsTable;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

test('seller listings page is accessible to approved sellers', function (): void {
    $user = User::factory()->seller()->create();

    Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Seller',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $this->actingAs($user)
        ->get('/seller/listings')
        ->assertOk()
        ->assertSee('Quản lý listing và key');
});

test('seller can create a listing from an existing variant and reuse a seller product', function (): void {
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'KeyCove Seller',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $adminProduct = Product::factory()->create([
        'name'   => 'Admin Product',
        'status' => GeneralStatus::Active,
    ]);

    $adminVariant = ProductVariant::factory()->create([
        'product_id'  => $adminProduct->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
        'edition'     => 'Standard Edition',
    ]);

    Livewire::actingAs($sellerUser)
        ->test(SellerListings::class)
        ->set('createMode', 'existing_variant')
        ->set('selectedVariantId', $adminVariant->id)
        ->set('listingForm.display_name', 'Admin Variant Listing')
        ->set('listingForm.price', 199000)
        ->set('listingForm.status', ProductListingStatus::Pending->value)
        ->call('saveListing')
        ->assertHasNoErrors();

    expect(ProductListing::query()->where('seller_id', $seller->id)->where('display_name', 'Admin Variant Listing')->exists())->toBeTrue();
});

test('seller can create a new product and keeps child records hidden until approval', function (): void {
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'KeyCove Seller',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    Livewire::actingAs($sellerUser)
        ->test(SellerListings::class)
        ->set('createMode', 'new_product')
        ->set('productForm.name', 'My Seller Product')
        ->set('productForm.slug', 'my-seller-product')
        ->set('productForm.publisher', 'KeyCove')
        ->set('productForm.developer', 'KeyCove Studio')
        ->set('productForm.image', UploadedFile::fake()->image('thumbnail.jpg'))
        ->set('variantForm.region_id', $region->id)
        ->set('variantForm.platform_id', $platform->id)
        ->set('variantForm.os_id', $os->id)
        ->set('variantForm.edition', 'Deluxe Edition')
        ->set('listingForm.display_name', 'My First Seller Listing')
        ->set('listingForm.price', 299000)
        ->call('saveListing')
        ->assertHasNoErrors();

    $sellerProduct = Product::query()
        ->where('submitted_by_seller_id', $seller->id)
        ->where('slug', 'my-seller-product')
        ->firstOrFail();

    $sellerVariant = ProductVariant::query()
        ->where('product_id', $sellerProduct->id)
        ->firstOrFail();

    $sellerListing = ProductListing::query()
        ->where('variant_id', $sellerVariant->id)
        ->firstOrFail();

    expect($sellerProduct->status)->toBe(GeneralStatus::Inactive)
        ->and($sellerProduct->image_thumbnail_path)->not->toBeEmpty()
        ->and($sellerVariant->status)->toBe(ProductVariantStatus::Draft)
        ->and($sellerListing->status)->toBe(ProductListingStatus::Draft);
});

test('seller cannot use another sellers product to create a variant or listing', function (): void {
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'My Shop',
        'cccd_number'         => '123456789014',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $otherSeller = Seller::query()->create([
        'user_id'             => User::factory()->seller()->create()->id,
        'shop_name'           => 'Other Shop',
        'cccd_number'         => '123456789013',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $otherProduct = Product::factory()->create([
        'submitted_by_seller_id' => $otherSeller->id,
        'status'                 => GeneralStatus::Inactive,
    ]);

    $allowedProductIds = Product::query()
        ->select(['id', 'submitted_by_seller_id', 'status'])
        ->withoutTrashed()
        ->where(function ($query) use ($seller): void {
            $query->where(function ($adminQuery): void {
                $adminQuery->whereNull('submitted_by_seller_id')
                    ->where('status', GeneralStatus::Active);
            })->orWhere(function ($sellerQuery) use ($seller): void {
                $sellerQuery->where('submitted_by_seller_id', $seller->id)
                    ->where('status', '!=', GeneralStatus::Deleted->value);
            });
        })
        ->pluck('id');

    expect($allowedProductIds)->not->toContain($otherProduct->id);
});

test('seller listings can only toggle between active and hidden', function (): void {
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'KeyCove Seller',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $product = Product::query()->create([
        'submitted_by_seller_id' => $seller->id,
        'name'                   => 'Seller Product',
        'slug'                   => 'seller-product',
        'status'                 => GeneralStatus::Active,
    ]);

    $variant = ProductVariant::query()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'edition'     => 'Standard',
        'status'      => ProductVariantStatus::Active,
    ]);

    $listing = ProductListing::query()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => $seller->id,
        'display_name' => 'Seller Listing',
        'slug'         => 'seller-listing',
        'price'        => 199000,
        'status'       => ProductListingStatus::Active,
    ]);

    Livewire::actingAs($sellerUser)
        ->test(SellerListingsTable::class)
        ->call('performToggleListingStatus', $listing->id)
        ->assertHasNoErrors();

    expect($listing->refresh()->status)->toBe(ProductListingStatus::Hidden);

    Livewire::actingAs($sellerUser)
        ->test(SellerListingsTable::class)
        ->call('performToggleListingStatus', $listing->id)
        ->assertHasNoErrors();

    expect($listing->refresh()->status)->toBe(ProductListingStatus::Active);

    $inactiveProduct = Product::query()->create([
        'submitted_by_seller_id' => $seller->id,
        'name'                   => 'Inactive Product',
        'slug'                   => 'inactive-product',
        'status'                 => GeneralStatus::Inactive,
    ]);

    $inactiveVariant = ProductVariant::query()->create([
        'product_id'  => $inactiveProduct->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'edition'     => 'Standard',
        'status'      => ProductVariantStatus::Draft,
    ]);

    $inactiveListing = ProductListing::query()->create([
        'variant_id'   => $inactiveVariant->id,
        'seller_id'    => $seller->id,
        'display_name' => 'Inactive Listing',
        'slug'         => 'inactive-listing',
        'price'        => 99000,
        'status'       => ProductListingStatus::Draft,
    ]);

    Livewire::actingAs($sellerUser)
        ->test(SellerListingsTable::class)
        ->call('performToggleListingStatus', $inactiveListing->id)
        ->assertHasNoErrors();

    expect($inactiveListing->refresh()->status)->toBe(ProductListingStatus::Draft);
});

test('admin activating a seller product activates its children', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'KeyCove Seller',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $product = Product::query()->create([
        'submitted_by_seller_id' => $seller->id,
        'name'                   => 'Seller Product',
        'slug'                   => 'seller-product-admin',
        'status'                 => GeneralStatus::Inactive,
    ]);

    $variant = ProductVariant::query()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'edition'     => 'Standard',
        'status'      => ProductVariantStatus::Draft,
    ]);

    $listing = ProductListing::query()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => $seller->id,
        'display_name' => 'Seller Listing',
        'slug'         => 'seller-listing-admin',
        'price'        => 199000,
        'status'       => ProductListingStatus::Draft,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(ProductTable::class)
        ->call('performToggleStatus', $product->id)
        ->assertHasNoErrors();

    expect($product->refresh()->status)->toBe(GeneralStatus::Active)
        ->and($variant->refresh()->status)->toBe(ProductVariantStatus::Active)
        ->and($listing->refresh()->status)->toBe(ProductListingStatus::Active);
});
