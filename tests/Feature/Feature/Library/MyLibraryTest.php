<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Events\ComplaintThreadUpdated;
use App\Livewire\Shop\Complaint\Thread as ComplaintThread;
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
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use App\Notifications\ComplaintActivityNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('authenticated user can access the my library page', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->assertSee('My Library');
});

test('verified users can start seller onboarding from my library', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->assertSee('Become a seller')
        ->assertSee(route('seller.apply'));
});

test('approved sellers can open the seller dashboard from my library', function (): void {
    $user = User::factory()->seller()->create();
    Seller::query()->create([
        'user_id'             => $user->id,
        'shop_name'           => 'KeyCove Store',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->assertSee('Open dashboard')
        ->assertSee(route('seller.dashboard.index'));
});

test('seller dashboard redirects unverified users back to profile verification', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/seller/dashboard')
        ->assertRedirect('/my-profile?section=security')
        ->assertSessionHas('profile-status', 'Verify your email first to unlock seller onboarding.');
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
        ->assertSee('Complaints can only be opened for delivered or disputing items.');
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
        ->assertSet('confirmReceivedOrderItemId', null)
        ->assertSee('Write review');

    expect($orderItem->refresh()->status)->toBe(OrderStatus::Completed);
});

test('authenticated user can leave a review for a completed order item', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create([
        'payment_status' => PaymentStatus::Completed,
    ]);
    $listing = createAdminListingForLibrary();

    Storage::fake(config('filesystems.public_disk'));

    $reviewImage = UploadedFile::fake()->image('review-proof.png');
    $reviewDocument = UploadedFile::fake()->create('invoice.pdf', 120, 'application/pdf');

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Reviewed Game Key',
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
        ->assertSee('Write review')
        ->call('openReviewForm', $orderItem->id)
        ->assertSet('reviewOrderItemId', $orderItem->id)
        ->set('reviewRating', '4')
        ->set('reviewComment', 'Fast delivery and the key worked right away.')
        ->set('reviewMedia', [$reviewImage, $reviewDocument])
        ->call('submitReview')
        ->assertHasNoErrors()
        ->assertSet('reviewOrderItemId', null)
        ->assertSee('Your review has been submitted.')
        ->assertSee('Reviewed');

    $review = Review::query()->where('order_item_id', $orderItem->id)->first();

    expect($review)->not->toBeNull();
    expect($review?->rating)->toBe(4);
    expect($review?->comment)->toBe('Fast delivery and the key worked right away.');
    expect($review?->media)->toHaveCount(2);
    expect($orderItem->refresh()->review)->not->toBeNull();

    foreach ($review?->media ?? [] as $path) {
        Storage::disk(config('filesystems.public_disk'))->assertExists($path);
    }
});

test('authenticated user cannot submit a second review for the same order item', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create([
        'payment_status' => PaymentStatus::Completed,
    ]);
    $listing = createAdminListingForLibrary();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'product_name_snapshot' => 'Duplicate Review Key',
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
        ->call('openReviewForm', $orderItem->id)
        ->set('reviewRating', '5')
        ->set('reviewComment', 'Great purchase.')
        ->call('submitReview')
        ->assertHasNoErrors()
        ->assertSee('Your review has been submitted.');

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->set('reviewOrderItemId', $orderItem->id)
        ->set('reviewRating', '1')
        ->set('reviewComment', 'Trying to edit the review.')
        ->call('submitReview')
        ->assertHasNoErrors()
        ->assertSee('You have already reviewed this item.');

    $reviews = Review::query()->where('order_item_id', $orderItem->id)->get();

    expect($reviews)->toHaveCount(1);
    expect($reviews->first()->rating)->toBe(5);
    expect($reviews->first()->comment)->toBe('Great purchase.');
});

test('authenticated user can view and reply to an existing complaint thread', function (): void {
    Notification::fake();
    Event::fake([ComplaintThreadUpdated::class]);

    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();
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
        'buyer_key_viewed_at'   => now(),
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
        ->assertRedirect(route('app.library.complaints.show', ['complaint' => Complaint::query()->where('order_item_id', $orderItem->id)->firstOrFail()->complaint_code]));

    $complaint = Complaint::query()->where('order_item_id', $orderItem->id)->firstOrFail();

    expect($complaint->evidence)->toHaveCount(1);
    expect($complaint->messages()->count())->toBe(1);

    Notification::assertSentTo($admin, ComplaintActivityNotification::class);

    Storage::disk(config('filesystems.public_disk'))->assertExists($complaint->evidence[0]);

    Livewire::actingAs($user)
        ->test(ComplaintThread::class, ['complaint' => $complaint])
        ->assertSee('Complaint thread')
        ->set('replyMessage', 'I have another screenshot showing the mismatch.')
        ->set('replyAttachments', [$replyFile])
        ->call('reply')
        ->assertHasNoErrors()
        ->assertSee('Your message has been added to the complaint thread.');

    Event::assertDispatched(ComplaintThreadUpdated::class, function (ComplaintThreadUpdated $event) use ($complaint): bool {
        return $event->complaintId === $complaint->id
            && $event->action === 'replied';
    });

    expect($complaint->fresh()->messages()->count())->toBe(2);
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
