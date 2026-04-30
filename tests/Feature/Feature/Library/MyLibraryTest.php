<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Livewire\Shop\Library\MyLibrary;
use App\Models\Complaint;
use App\Models\OperatingSystem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('authenticated user can access the my library page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/my-library');

    $response->assertOk();
    $response->assertSeeLivewire(MyLibrary::class);
    $response->assertSee('My Library');
});

test('legacy orders route redirects to my library', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/orders')
        ->assertRedirect('/my-library');
});

test('authenticated user can reveal purchased keys from my library', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create();
    $listing = createAdminListingForLibrary();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Library Game Key',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => $listing->price,
        'subtotal'              => $listing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Delivered,
    ]);

    $productKey = ProductKey::factory()->withListing($listing)->sold()->create([
        'order_item_id' => $orderItem->id,
    ]);

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->call('promptKeyReveal', $orderItem->id)
        ->set('keyAccessPassword', 'password')
        ->call('revealOrderItemKeys')
        ->assertHasNoErrors()
        ->assertSet('keyAccessOrderItemId', null)
        ->assertSee($productKey->key_code)
        ->call('hideOrderItemKeys', $orderItem->id)
        ->assertSee('****')
        ->assertDontSee($productKey->key_code)
        ->call('toggleOrderItemKeys', $orderItem->id)
        ->assertSee($productKey->key_code);
});

test('authenticated user can open a variant details modal from my library', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create();
    $listing = createAdminListingForLibrary();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'order_item_code'       => 'OI-20260430-LIBMOD',
        'product_name_snapshot' => 'Library Game Key',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => $listing->price,
        'subtotal'              => $listing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Delivered,
    ]);

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->call('openItemDetails', $orderItem->id)
        ->assertSet('viewingOrderItemId', $orderItem->id)
        ->assertSee('Variant details')
        ->assertSee('Standard Edition')
        ->assertSee('Library Listing')
        ->assertSee('OI-20260430-LIBMOD');
});

test('items without first key reveal do not show confirmation or complaint actions', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create([
        'payment_status' => PaymentStatus::Completed,
    ]);
    $listing = createAdminListingForLibrary();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Locked Game Key',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => $listing->price,
        'subtotal'              => $listing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Delivered,
    ]);

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->assertDontSee('Confirm received')
        ->assertDontSee('Open complaint')
        ->call('confirmReceived', $orderItem->id)
        ->assertSee('Open the key once before confirming receipt.');

    expect($orderItem->refresh()->status)->toBe(OrderStatus::Delivered);
});

test('completed items cannot open a new complaint', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create([
        'payment_status' => PaymentStatus::Completed,
    ]);
    $listing = createAdminListingForLibrary();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Completed Game Key',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => $listing->price,
        'subtotal'              => $listing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Completed,
    ]);

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->assertDontSee('Open complaint')
        ->call('openComplaintForm', $orderItem->id)
        ->assertSet('complaintOrderItemId', null)
        ->assertSee('Completed items can no longer be disputed.');
});

test('authenticated user can confirm received through a modal', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create([
        'payment_status' => PaymentStatus::Completed,
    ]);
    $listing = createAdminListingForLibrary();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Delivered Game Key',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => $listing->price,
        'subtotal'              => $listing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Delivered,
    ]);

    $productKey = ProductKey::factory()->withListing($listing)->sold()->create([
        'order_item_id' => $orderItem->id,
    ]);

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->call('promptKeyReveal', $orderItem->id)
        ->set('keyAccessPassword', 'password')
        ->call('revealOrderItemKeys')
        ->assertHasNoErrors()
        ->assertSee($productKey->key_code)
        ->call('openConfirmReceivedModal', $orderItem->id)
        ->assertSet('confirmReceivedOrderItemId', $orderItem->id)
        ->assertSee('Mark this order as completed?')
        ->call('confirmReceived', $orderItem->id)
        ->assertSet('confirmReceivedOrderItemId', null);

    expect($orderItem->refresh()->status)->toBe(OrderStatus::Completed);
});

test('authenticated user can view and reply to an existing complaint thread', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create();
    $listing = createAdminListingForLibrary();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'order_item_code'       => 'OI-20260430-COMPLAINT',
        'product_name_snapshot' => 'Library Game Key',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => $listing->price,
        'subtotal'              => $listing->price,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Disputing,
    ]);

    Storage::fake(config('filesystems.public_disk'));

    $evidenceFile = UploadedFile::fake()->create('proof.png', 120, 'image/png');
    $replyFile = UploadedFile::fake()->create('follow-up.pdf', 120, 'application/pdf');

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->call('openComplaintForm', $orderItem->id)
        ->set('complaintReason', 'The key does not match the product description.')
        ->set('complaintEvidence', [$evidenceFile])
        ->call('submitComplaint')
        ->assertHasNoErrors()
        ->assertSee('Complaint details')
        ->set('complaintReplyMessage', 'I have another screenshot showing the mismatch.')
        ->set('complaintReplyAttachments', [$replyFile])
        ->call('replyComplaint')
        ->assertHasNoErrors()
        ->assertSee('I have another screenshot showing the mismatch.')
        ->assertSee('Your message has been added to the complaint thread.');

    $complaint = Complaint::query()->where('order_item_id', $orderItem->id)->firstOrFail();

    expect($complaint->evidence)->toHaveCount(1);
    expect($complaint->messages()->count())->toBe(2);

    Storage::disk(config('filesystems.public_disk'))->assertExists($complaint->evidence[0]);
    Storage::disk(config('filesystems.public_disk'))->assertExists($complaint->messages()->latest('id')->first()->attachments[0]);
});

function createAdminListingForLibrary(): ProductListing
{
    $region = Region::factory()->create(['status' => GeneralStatus::Active]);
    $platform = Platform::factory()->create(['status' => GeneralStatus::Active]);
    $os = OperatingSystem::factory()->create(['status' => GeneralStatus::Active]);

    $product = Product::factory()->create([
        'name'   => 'Library Product',
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

    return ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Library Listing',
        'price'        => 149000,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);
}
