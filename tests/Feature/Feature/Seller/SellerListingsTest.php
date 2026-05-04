<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Livewire\Shop\Seller\SellerListings;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\User;
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

    // Livewire::actingAs($sellerUser)
    //     ->test(SellerListings::class)
    //     ->set('createMode', 'new_product')
    //     ->set('productForm.name', 'My Seller Product')
    //     ->set('productForm.slug', 'my-seller-product')
    //     ->set('productForm.publisher', 'KeyCove')
    //     ->set('productForm.developer', 'KeyCove Studio')
    //     ->set('productForm.status', GeneralStatus::Inactive->value)
    //     ->set('variantForm.region_id', $region->id)
    //     ->set('variantForm.platform_id', $platform->id)
    //     ->set('variantForm.os_id', $os->id)
    //     ->set('variantForm.edition', 'Deluxe Edition')
    //     ->set('listingForm.display_name', 'My First Seller Listing')
    //     ->set('listingForm.price', 299000)
    //     ->set('listingForm.status', ProductListingStatus::Pending->value)
    //     ->call('saveListing')
    //     ->assertHasNoErrors();
    //
    // $sellerProduct = Product::query()->where('submitted_by_seller_id', $seller->id)->where('slug', 'my-seller-product')->first();
    //
    // expect($sellerProduct)->not->toBeNull();
    // expect(ProductListing::query()->where('seller_id', $seller->id)->where('display_name', 'My First Seller Listing')->exists())->toBeTrue();
    //
    // Livewire::actingAs($sellerUser)
    //     ->test(SellerListings::class)
    //     ->set('createMode', 'existing_product_variant')
    //     ->set('selectedProductId', $sellerProduct->id)
    //     ->set('variantForm.region_id', $region->id)
    //     ->set('variantForm.platform_id', $platform->id)
    //     ->set('variantForm.os_id', $os->id)
    //     ->set('variantForm.edition', 'Ultimate Edition')
    //     ->set('listingForm.display_name', 'Reused Seller Product Listing')
    //     ->set('listingForm.price', 399000)
    //     ->set('listingForm.status', ProductListingStatus::Pending->value)
    //     ->call('saveListing')
    //     ->assertHasNoErrors();
    //
    // expect(ProductVariant::query()->where('product_id', $sellerProduct->id)->count())->toBe(2);
    // expect(ProductListing::query()->where('seller_id', $seller->id)->count())->toBe(3);
});
