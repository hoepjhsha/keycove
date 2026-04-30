<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\User;

test('seller browse hides the signed in sellers own listings', function (): void {
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'Own Seller Shop',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $ownProduct = Product::factory()->create(['name' => 'Own Product', 'status' => GeneralStatus::Active]);
    $ownVariant = ProductVariant::factory()->create([
        'product_id'  => $ownProduct->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
    ]);
    ProductListing::factory()->create([
        'variant_id'   => $ownVariant->id,
        'seller_id'    => $seller->id,
        'display_name' => 'My Hidden Listing',
        'price'        => 199000,
        'stock_count'  => 1,
        'status'       => ProductListingStatus::Active,
    ]);

    $otherSeller = Seller::query()->create([
        'user_id'             => User::factory()->seller()->create()->id,
        'shop_name'           => 'Other Seller Shop',
        'cccd_number'         => '123456789013',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $otherProduct = Product::factory()->create(['name' => 'Visible Product', 'status' => GeneralStatus::Active]);
    $otherVariant = ProductVariant::factory()->create([
        'product_id'  => $otherProduct->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
    ]);
    ProductListing::factory()->create([
        'variant_id'   => $otherVariant->id,
        'seller_id'    => $otherSeller->id,
        'display_name' => 'Visible Listing',
        'price'        => 299000,
        'stock_count'  => 1,
        'status'       => ProductListingStatus::Active,
    ]);

    $this->actingAs($sellerUser)
        ->get('/sellers')
        ->assertOk()
        ->assertSee('Visible Listing')
        ->assertDontSee('My Hidden Listing');
});
