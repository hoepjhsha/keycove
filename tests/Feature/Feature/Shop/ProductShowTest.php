<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Livewire\Shop\Product\ProductShow;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\OperatingSystem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Review;
use App\Models\Seller;
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

it('renders the detail page for a seller listing', function (): void {
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $product = Product::factory()->withCategories(Category::factory()->create(['status' => GeneralStatus::Active]))->create([
        'name'   => 'Seller Product',
        'slug'   => 'seller-product',
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

    $seller = Seller::query()->create([
        'user_id'          => User::factory()->create()->id,
        'shop_name'        => 'Seller Shop',
        'cccd_number'      => '123456789012',
        'cccd_front_image' => 'sellers/test-front.jpg',
        'cccd_back_image'  => 'sellers/test-back.jpg',
        'kyc_status'       => 1,
    ]);

    $listing = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => $seller->id,
        'display_name' => 'Seller Bundle',
        'price'        => 149000,
        'stock_count'  => 2,
        'status'       => ProductListingStatus::Active,
    ]);

    $response = $this->get(route('app.products.show', ['product' => $product->slug, 'listing' => $listing->slug]));

    $response->assertOk();
    $response->assertSee('Seller Bundle');
});

it('shows reviews for the current product on the detail page', function (): void {
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $product = Product::factory()->create([
        'name'   => 'Reviewed Product',
        'slug'   => 'reviewed-product',
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
        'display_name' => 'Reviewed Bundle',
        'price'        => 123456,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);

    $buyerOne = User::factory()->create();
    $buyerTwo = User::factory()->create();
    $otherProduct = Product::factory()->create([
        'name'   => 'Other Product',
        'slug'   => 'other-product',
        'status' => GeneralStatus::Active,
    ]);
    $otherVariant = ProductVariant::factory()->create([
        'product_id'  => $otherProduct->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
        'edition'     => 'Standard Edition',
    ]);
    $otherListing = ProductListing::factory()->create([
        'variant_id'   => $otherVariant->id,
        'seller_id'    => null,
        'display_name' => 'Other Bundle',
        'price'        => 99999,
        'stock_count'  => 2,
        'status'       => ProductListingStatus::Active,
    ]);

    $sameProductOtherListing = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Same Product Other Bundle',
        'price'        => 88888,
        'stock_count'  => 2,
        'status'       => ProductListingStatus::Active,
    ]);

    $reviewOneItem = OrderItem::query()->create([
        'order_id'              => Order::factory()->forBuyer($buyerOne)->create(['payment_status' => PaymentStatus::Completed])->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Reviewed Product Key 1',
        'variant_snapshot'      => ['variant_id' => $variant->id],
        'quantity'              => 1,
        'unit_price'            => $listing->price,
        'subtotal'              => $listing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Completed,
    ]);

    $reviewTwoItem = OrderItem::query()->create([
        'order_id'              => Order::factory()->forBuyer($buyerTwo)->create(['payment_status' => PaymentStatus::Completed])->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Reviewed Product Key 2',
        'variant_snapshot'      => ['variant_id' => $variant->id],
        'quantity'              => 1,
        'unit_price'            => $listing->price,
        'subtotal'              => $listing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Completed,
    ]);

    $otherItem = OrderItem::query()->create([
        'order_id'              => Order::factory()->forBuyer(User::factory()->create())->create(['payment_status' => PaymentStatus::Completed])->id,
        'listing_id'            => $otherListing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Other Product Key',
        'variant_snapshot'      => ['variant_id' => $otherVariant->id],
        'quantity'              => 1,
        'unit_price'            => $otherListing->price,
        'subtotal'              => $otherListing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Completed,
    ]);

    $sameProductOtherItem = OrderItem::query()->create([
        'order_id'              => Order::factory()->forBuyer(User::factory()->create())->create(['payment_status' => PaymentStatus::Completed])->id,
        'listing_id'            => $sameProductOtherListing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Same Product Other Key',
        'variant_snapshot'      => ['variant_id' => $variant->id],
        'quantity'              => 1,
        'unit_price'            => $sameProductOtherListing->price,
        'subtotal'              => $sameProductOtherListing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Completed,
    ]);

    Review::query()->create([
        'user_id'       => $buyerOne->id,
        'order_item_id' => $reviewOneItem->id,
        'rating'        => 5,
        'comment'       => 'Perfect delivery.',
        'media'         => ['reviews/media/review-one.png'],
    ]);

    Review::query()->create([
        'user_id'       => $buyerTwo->id,
        'order_item_id' => $reviewTwoItem->id,
        'rating'        => 4,
        'comment'       => 'Worked as expected.',
        'media'         => [],
    ]);

    Review::query()->create([
        'user_id'       => $otherItem->order->buyer_id,
        'order_item_id' => $otherItem->id,
        'rating'        => 1,
        'comment'       => 'Unrelated product review.',
        'media'         => [],
    ]);

    Review::query()->create([
        'user_id'       => $sameProductOtherItem->order->buyer_id,
        'order_item_id' => $sameProductOtherItem->id,
        'rating'        => 2,
        'comment'       => 'Review for another listing of the same product.',
        'media'         => [],
    ]);

    $response = $this->get(route('app.products.show', ['product' => $product->slug, 'listing' => $listing->slug]));

    $response->assertOk();
    $response->assertSee('Reviews');
    $response->assertSee('2 reviews');
    $response->assertSee('4.5');
    $response->assertSee('Perfect delivery.');
    $response->assertSee('Worked as expected.');
    $response->assertDontSee('Unrelated product review.');
    $response->assertDontSee('Review for another listing of the same product.');
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
