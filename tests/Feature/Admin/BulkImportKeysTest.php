<?php

use App\Enums\GeneralStatus;
use App\Enums\KycStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Action\ProductKey\BulkImportIndex;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

it('imports product keys using the listing slug', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $product = Product::factory()->create(['status' => GeneralStatus::Active]);
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $operatingSystem = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);
    $seller = Seller::create([
        'user_id'          => User::factory()->seller()->create()->id,
        'shop_name'        => 'Demo Shop',
        'cccd_number'      => '123456789012',
        'cccd_front_image' => null,
        'cccd_back_image'  => null,
        'kyc_status'       => KycStatus::Approved,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $operatingSystem->id,
        'status'      => ProductVariantStatus::Active,
    ]);

    $listing = ProductListing::factory()->create([
        'variant_id' => $variant->id,
        'seller_id'  => $seller->id,
        'slug'       => 'demo-listing-slug',
        'status'     => ProductListingStatus::Active,
    ]);

    $csv = implode("\n", [
        'listing_slug,key_code,status',
        $listing->slug.',ABCDE-ABCDE-ABCDE-ABCDE-ABCDE,Available',
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(BulkImportIndex::class)
        ->set('importForm.file', UploadedFile::fake()->createWithContent('keys.csv', $csv))
        ->call('processImport')
        ->assertSet('progressPercentage', 100);

    expect(ProductKey::query()->where('listing_id', $listing->id)->count())->toBe(1);
});
