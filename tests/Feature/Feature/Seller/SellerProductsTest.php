<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\ProductVariantStatus;
use App\Livewire\Shop\Seller\SellerProducts;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

function createApprovedSellerForProductsTest(): array
{
    $user = User::factory()->seller()->create();

    $seller = Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Seller',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    return [$user, $seller];
}

test('seller products page is accessible to approved sellers', function (): void {
    [$user] = createApprovedSellerForProductsTest();

    $this->actingAs($user)
        ->get('/seller/products')
        ->assertOk()
        ->assertSee('Sản phẩm của tôi');
});

test('seller can create a product before approval', function (): void {
    [$user, $seller] = createApprovedSellerForProductsTest();

    Livewire::actingAs($user)
        ->test(SellerProducts::class)
        ->call('openCreateProductModal')
        ->set('createForm.name', 'My Seller Product')
        ->set('createForm.slug', 'my-seller-product')
        ->set('createForm.publisher', 'KeyCove')
        ->set('createForm.developer', 'KeyCove Studio')
        ->set('createForm.image', UploadedFile::fake()->create('thumbnail.jpg', 120, 'image/jpeg'))
        ->call('saveProduct')
        ->assertHasNoErrors();

    $product = Product::query()
        ->where('submitted_by_seller_id', $seller->id)
        ->where('slug', 'my-seller-product')
        ->firstOrFail();

    expect($product->status)->toBe(GeneralStatus::Inactive)
        ->and($product->image_thumbnail_path)->not->toBeEmpty();
});

test('seller can edit only inactive products', function (): void {
    [$user, $seller] = createApprovedSellerForProductsTest();

    $product = Product::query()->create([
        'submitted_by_seller_id' => $seller->id,
        'name'                   => 'Draft Product',
        'slug'                   => 'draft-product',
        'status'                 => GeneralStatus::Inactive,
    ]);

    Livewire::actingAs($user)
        ->test(SellerProducts::class)
        ->call('openEditProductModal', $product->id)
        ->set('editForm.name', 'Updated Draft Product')
        ->call('saveProduct')
        ->assertHasNoErrors();

    expect($product->refresh()->name)->toBe('Updated Draft Product');
});

test('seller cannot edit an active product', function (): void {
    [$user, $seller] = createApprovedSellerForProductsTest();

    $product = Product::query()->create([
        'submitted_by_seller_id' => $seller->id,
        'name'                   => 'Active Product',
        'slug'                   => 'active-product',
        'status'                 => GeneralStatus::Active,
    ]);

    Livewire::actingAs($user)
        ->test(SellerProducts::class)
        ->set('editingProductId', $product->id)
        ->set('editForm.name', 'Changed Name')
        ->call('saveProduct')
        ->assertHasErrors(['product']);

    expect($product->refresh()->name)->toBe('Active Product');
});

test('seller can add variant to their product and defaults status by product approval', function (): void {
    [$user, $seller] = createApprovedSellerForProductsTest();

    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $inactiveProduct = Product::query()->create([
        'submitted_by_seller_id' => $seller->id,
        'name'                   => 'Inactive Product',
        'slug'                   => 'inactive-product-seller',
        'status'                 => GeneralStatus::Inactive,
    ]);

    Livewire::actingAs($user)
        ->test(SellerProducts::class)
        ->call('openVariantModal', $inactiveProduct->id)
        ->set('variantForm.region_id', $region->id)
        ->set('variantForm.platform_id', $platform->id)
        ->set('variantForm.os_id', $os->id)
        ->set('variantForm.edition', 'Standard Edition')
        ->call('saveVariant')
        ->assertHasNoErrors();

    $inactiveVariant = ProductVariant::query()->where('product_id', $inactiveProduct->id)->firstOrFail();
    expect($inactiveVariant->status)->toBe(ProductVariantStatus::Draft);

    $activeProduct = Product::query()->create([
        'submitted_by_seller_id' => $seller->id,
        'name'                   => 'Active Product',
        'slug'                   => 'active-product-seller',
        'status'                 => GeneralStatus::Active,
    ]);

    Livewire::actingAs($user)
        ->test(SellerProducts::class)
        ->call('openVariantModal', $activeProduct->id)
        ->set('variantForm.region_id', $region->id)
        ->set('variantForm.platform_id', $platform->id)
        ->set('variantForm.os_id', $os->id)
        ->set('variantForm.edition', 'Deluxe Edition')
        ->call('saveVariant')
        ->assertHasNoErrors();

    $activeVariant = ProductVariant::query()->where('product_id', $activeProduct->id)->latest('id')->firstOrFail();
    expect($activeVariant->status)->toBe(ProductVariantStatus::Active);
});

test('seller can delete their variant', function (): void {
    [$user, $seller] = createApprovedSellerForProductsTest();

    $product = Product::query()->create([
        'submitted_by_seller_id' => $seller->id,
        'name'                   => 'Deletable Product',
        'slug'                   => 'deletable-product',
        'status'                 => GeneralStatus::Inactive,
    ]);

    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $variant = ProductVariant::query()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'edition'     => 'Standard',
        'status'      => ProductVariantStatus::Draft,
    ]);

    Livewire::actingAs($user)
        ->test(SellerProducts::class)
        ->call('performDeleteVariant', $variant->id)
        ->assertHasNoErrors();

    expect(ProductVariant::withTrashed()->findOrFail($variant->id)->trashed())->toBeTrue()
        ->and(ProductVariant::withTrashed()->findOrFail($variant->id)->status)->toBe(ProductVariantStatus::Deleted);
});

test('seller can restore their variant', function (): void {
    [$user, $seller] = createApprovedSellerForProductsTest();

    $product = Product::query()->create([
        'submitted_by_seller_id' => $seller->id,
        'name'                   => 'Restorable Product',
        'slug'                   => 'restorable-product',
        'status'                 => GeneralStatus::Inactive,
    ]);

    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $variant = ProductVariant::query()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'edition'     => 'Standard',
        'status'      => ProductVariantStatus::Deleted,
    ]);

    $variant->delete();

    Livewire::actingAs($user)
        ->test(SellerProducts::class)
        ->call('performRestoreVariant', $variant->id)
        ->assertHasNoErrors();

    expect(ProductVariant::withTrashed()->findOrFail($variant->id)->trashed())->toBeFalse()
        ->and($variant->refresh()->status)->toBe(ProductVariantStatus::Draft);
});

test('seller can delete their product and the product children become hidden', function (): void {
    [$user, $seller] = createApprovedSellerForProductsTest();

    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $product = Product::query()->create([
        'submitted_by_seller_id' => $seller->id,
        'name'                   => 'Deletable Product',
        'slug'                   => 'deletable-product',
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

    Livewire::actingAs($user)
        ->test(SellerProducts::class)
        ->call('performDeleteProduct', $product->id)
        ->assertHasNoErrors();

    $deletedProduct = Product::withTrashed()->findOrFail($product->id);

    expect($deletedProduct->trashed())->toBeTrue()
        ->and($deletedProduct->status)->toBe(GeneralStatus::Deleted)
        ->and($variant->refresh()->status)->toBe(ProductVariantStatus::Draft);
});

test('seller cannot add a variant to another sellers product', function (): void {
    [$user, $seller] = createApprovedSellerForProductsTest();

    $otherUser = User::factory()->seller()->create();
    $otherSeller = Seller::query()->create([
        'user_id'             => $otherUser->id,
        'shop_name'           => 'Other Shop',
        'cccd_number'         => '123456789013',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $otherProduct = Product::query()->create([
        'submitted_by_seller_id' => $otherSeller->id,
        'name'                   => 'Other Product',
        'slug'                   => 'other-product',
        'status'                 => GeneralStatus::Inactive,
    ]);

    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    Livewire::actingAs($user)
        ->test(SellerProducts::class)
        ->set('variantProductId', $otherProduct->id)
        ->set('variantForm.product_id', $otherProduct->id)
        ->set('variantForm.region_id', $region->id)
        ->set('variantForm.platform_id', $platform->id)
        ->set('variantForm.os_id', $os->id)
        ->set('variantForm.edition', 'Standard Edition')
        ->call('saveVariant')
        ->assertHasErrors(['product']);

    expect(ProductVariant::query()->where('product_id', $otherProduct->id)->exists())->toBeFalse();
});
