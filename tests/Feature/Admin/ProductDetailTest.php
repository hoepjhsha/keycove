<?php

use App\Enums\GeneralStatus;
use App\Enums\KycStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Action\Product\ProductDetail;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->product = Product::factory()->create(['status' => GeneralStatus::Active]);
    $this->region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $this->platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $this->operatingSystem = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);
    $this->seller = Seller::create([
        'user_id'          => User::factory()->seller()->create()->id,
        'shop_name'        => 'Demo Shop',
        'cccd_number'      => '123456789012',
        'cccd_front_image' => null,
        'cccd_back_image'  => null,
        'kyc_status'       => KycStatus::Approved,
    ]);
});

it('shows listing slug in the product detail page', function (): void {
    $variant = ProductVariant::factory()->create([
        'product_id'  => $this->product->id,
        'region_id'   => $this->region->id,
        'platform_id' => $this->platform->id,
        'os_id'       => $this->operatingSystem->id,
        'status'      => ProductVariantStatus::Active,
    ]);

    $listing = ProductListing::factory()->create([
        'variant_id' => $variant->id,
        'seller_id'  => $this->seller->id,
        'slug'       => 'demo-listing-slug',
        'status'     => ProductListingStatus::Active,
    ]);

    Livewire::actingAs($this->admin, 'admin')
        ->test(ProductDetail::class, ['id' => $this->product->id])
        ->call('toggleVariantRow', $variant->id)
        ->assertSee($listing->slug);
});
