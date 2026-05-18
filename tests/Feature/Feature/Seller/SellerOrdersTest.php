<?php

declare(strict_types=1);

use App\Enums\ComplaintStatus;
use App\Enums\GeneralStatus;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Livewire\Shop\Seller\OrderIndex;
use App\Models\Complaint;
use App\Models\OperatingSystem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\User;
use Livewire\Livewire;

function createApprovedSeller(): array
{
    $user = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'Seller Shop',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    return [$user, $seller];
}

it('shows seller orders page for approved sellers', function (): void {
    [$user] = createApprovedSeller();

    $this->actingAs($user)
        ->get('/seller/orders')
        ->assertSuccessful()
        ->assertSeeLivewire(OrderIndex::class)
        ->assertSee('Quản lý order item theo seller');
});

it('only shows order items belonging to the seller', function (): void {
    [$sellerUser, $seller] = createApprovedSeller();
    $otherSellerUser = User::factory()->seller()->create();
    $otherSeller = Seller::query()->create([
        'user_id'             => $otherSellerUser->id,
        'shop_name'           => 'Other Shop',
        'cccd_number'         => '109876543210',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    $buyer = User::factory()->create();
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
    ]);
    $sellerListing = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => $seller->id,
        'display_name' => 'Seller Listing',
        'price'        => 199000,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);
    $otherListing = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => $otherSeller->id,
        'display_name' => 'Other Listing',
        'price'        => 299000,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);

    $sellerOrder = Order::factory()->forBuyer($buyer)->create([
        'payment_status' => PaymentStatus::Completed,
        'total_price'    => 199000,
    ]);

    $sellerItem = OrderItem::query()->create([
        'order_id'              => $sellerOrder->id,
        'listing_id'            => $sellerListing->id,
        'seller_id'             => $seller->id,
        'product_name_snapshot' => 'Seller Listing',
        'variant_snapshot'      => ['variant_id' => $variant->id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 19900,
        'seller_amount'         => 179100,
        'status'                => OrderStatus::Delivered,
    ]);

    $otherOrder = Order::factory()->forBuyer($buyer)->create([
        'payment_status' => PaymentStatus::Completed,
        'total_price'    => 299000,
    ]);

    OrderItem::query()->create([
        'order_id'              => $otherOrder->id,
        'listing_id'            => $otherListing->id,
        'seller_id'             => $otherSeller->id,
        'product_name_snapshot' => 'Other Listing',
        'variant_snapshot'      => ['variant_id' => $variant->id],
        'quantity'              => 1,
        'unit_price'            => 299000,
        'subtotal'              => 299000,
        'platform_fee'          => 29900,
        'seller_amount'         => 269100,
        'status'                => OrderStatus::Completed,
    ]);

    Complaint::query()->create([
        'order_item_id'  => $sellerItem->id,
        'complaint_code' => 'CMP-SELLER-001',
        'reason'         => 'Invalid key',
        'evidence'       => [],
        'status'         => ComplaintStatus::Open,
    ]);

    Livewire::actingAs($sellerUser)
        ->test(OrderIndex::class)
        ->assertSee('Seller Listing')
        ->assertSee('179.100 VND')
        ->assertSee('CMP-SELLER-001')
        ->assertDontSee('Other Listing')
        ->assertDontSee('269.100 VND');
});

it('rejects non approved sellers from order page', function (): void {
    $user = User::factory()->seller()->create();
    Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'Pending Shop',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Pending,
        'kyc_rejected_reason' => null,
    ]);

    $this->actingAs($user)
        ->get('/seller/orders')
        ->assertRedirect('/seller/apply');
});
