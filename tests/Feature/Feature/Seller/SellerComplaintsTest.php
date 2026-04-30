<?php

declare(strict_types=1);

use App\Enums\ComplaintStatus;
use App\Enums\GeneralStatus;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Events\ComplaintThreadUpdated;
use App\Livewire\Shop\Complaint\Thread as ComplaintThread;
use App\Models\Complaint;
use App\Models\ComplaintMessage;
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
use App\Notifications\ComplaintActivityNotification;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

test('seller can open their complaints index and complaint thread', function (): void {
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'Seller Shop',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    $buyer = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $region = Region::factory()->create([
        'status'    => GeneralStatus::Active,
        'slug'      => (string) str()->uuid(),
        'flag_code' => 'ZZ1',
    ]);

    $platform = Platform::factory()->create([
        'status' => GeneralStatus::Active,
        'slug'   => (string) str()->uuid(),
    ]);

    $os = OperatingSystem::factory()->create([
        'status' => GeneralStatus::Active,
        'slug'   => (string) str()->uuid(),
    ]);

    $product = Product::factory()->create([
        'status' => GeneralStatus::Active,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
    ]);

    $listing = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => $seller->id,
        'display_name' => 'Seller Listing',
        'price'        => 199000,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);

    $order = Order::factory()->forBuyer($buyer)->create();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'product_name_snapshot' => 'Seller Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 19900,
        'seller_amount'         => 179100,
        'status'                => OrderStatus::Disputing,
        'buyer_key_viewed_at'   => now(),
    ]);

    $complaint = Complaint::query()->create([
        'complaint_code' => 'CMP-THREAD-SELLER',
        'order_item_id'  => $orderItem->id,
        'reason'         => 'The key did not work after activation.',
        'evidence'       => [],
        'status'         => ComplaintStatus::Open,
    ]);

    ComplaintMessage::query()->create([
        'complaint_id' => $complaint->id,
        'sender_id'    => $buyer->id,
        'message'      => 'The key did not work after activation.',
        'attachments'  => [],
    ]);

    $this->actingAs($sellerUser)
        ->get(route('seller.complaints.index'))
        ->assertOk()
        ->assertSee('Seller complaints')
        ->assertSee('CMP-THREAD-SELLER');

    $this->actingAs($sellerUser)
        ->get(route('seller.complaints.show', ['complaint' => $complaint->complaint_code]))
        ->assertOk()
        ->assertSee('Complaint thread')
        ->assertSee('Buyer, seller, and admin');

    Livewire::actingAs($admin, 'admin')
        ->test(ComplaintThread::class, ['complaint' => $complaint])
        ->assertSee('Complaint thread')
        ->assertSee('Buyer, seller, and admin');
});

test('admin and seller can open complaint threads without a complaint code', function (): void {
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'Seller Shop',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    $buyer = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $region = Region::factory()->create([
        'status'    => GeneralStatus::Active,
        'slug'      => (string) str()->uuid(),
        'flag_code' => 'ZZ2',
    ]);

    $platform = Platform::factory()->create([
        'status' => GeneralStatus::Active,
        'slug'   => (string) str()->uuid(),
    ]);

    $os = OperatingSystem::factory()->create([
        'status' => GeneralStatus::Active,
        'slug'   => (string) str()->uuid(),
    ]);

    $product = Product::factory()->create([
        'status' => GeneralStatus::Active,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
    ]);

    $listing = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => $seller->id,
        'display_name' => 'Seller Listing',
        'price'        => 199000,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);

    $order = Order::factory()->forBuyer($buyer)->create();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'product_name_snapshot' => 'Seller Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 19900,
        'seller_amount'         => 179100,
        'status'                => OrderStatus::Disputing,
        'buyer_key_viewed_at'   => now(),
    ]);

    $complaint = Complaint::query()->create([
        'complaint_code' => null,
        'order_item_id'  => $orderItem->id,
        'reason'         => 'The key did not work after activation.',
        'evidence'       => [],
        'status'         => ComplaintStatus::Open,
    ]);

    ComplaintMessage::query()->create([
        'complaint_id' => $complaint->id,
        'sender_id'    => $buyer->id,
        'message'      => 'The key did not work after activation.',
        'attachments'  => [],
    ]);

    $this->actingAs($sellerUser)
        ->get('/seller/complaints/'.$complaint->id)
        ->assertOk()
        ->assertSee('Complaint thread');

    Livewire::actingAs($admin, 'admin')
        ->test(ComplaintThread::class, ['complaint' => $complaint])
        ->assertSee('Complaint thread');
});

test('admin can reply to complaint without a complaint code', function (): void {
    Notification::fake();
    Event::fake([ComplaintThreadUpdated::class]);

    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'Seller Shop',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    $buyer = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $region = Region::factory()->create([
        'status'    => GeneralStatus::Active,
        'slug'      => (string) str()->uuid(),
        'flag_code' => 'ZZ3',
    ]);

    $platform = Platform::factory()->create([
        'status' => GeneralStatus::Active,
        'slug'   => (string) str()->uuid(),
    ]);

    $os = OperatingSystem::factory()->create([
        'status' => GeneralStatus::Active,
        'slug'   => (string) str()->uuid(),
    ]);

    $product = Product::factory()->create([
        'status' => GeneralStatus::Active,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id'  => $product->id,
        'region_id'   => $region->id,
        'platform_id' => $platform->id,
        'os_id'       => $os->id,
        'status'      => ProductVariantStatus::Active,
    ]);

    $listing = ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => $seller->id,
        'display_name' => 'Seller Listing',
        'price'        => 199000,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);

    $order = Order::factory()->forBuyer($buyer)->create();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'product_name_snapshot' => 'Seller Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 19900,
        'seller_amount'         => 179100,
        'status'                => OrderStatus::Disputing,
        'buyer_key_viewed_at'   => now(),
    ]);

    $complaint = Complaint::query()->create([
        'complaint_code' => null,
        'order_item_id'  => $orderItem->id,
        'reason'         => 'The key did not work after activation.',
        'evidence'       => [],
        'status'         => ComplaintStatus::Open,
    ]);

    ComplaintMessage::query()->create([
        'complaint_id' => $complaint->id,
        'sender_id'    => $buyer->id,
        'message'      => 'The key did not work after activation.',
        'attachments'  => [],
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(ComplaintThread::class, ['complaint' => $complaint])
        ->set('replyMessage', 'Admin follow-up with update.')
        ->call('reply')
        ->assertHasNoErrors();

    Event::assertDispatched(ComplaintThreadUpdated::class, function (ComplaintThreadUpdated $event) use ($complaint): bool {
        return $event->complaintId === $complaint->id
            && $event->action === 'replied';
    });

    Notification::assertSentTo($buyer, ComplaintActivityNotification::class);
    Notification::assertSentTo($sellerUser, ComplaintActivityNotification::class);
});
