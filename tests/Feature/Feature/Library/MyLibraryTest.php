<?php

declare(strict_types=1);

use App\Enums\EscrowStatus;
use App\Enums\GeneralStatus;
use App\Enums\InternalWalletEntryType;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Enums\WalletType;
use App\Events\ComplaintThreadUpdated;
use App\Livewire\Shop\Complaint\Thread as ComplaintThread;
use App\Livewire\Shop\Library\MyLibrary;
use App\Models\Complaint;
use App\Models\Escrow;
use App\Models\InternalWalletEntry;
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
use App\Models\Wallet;
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
        ->assertSee('Thư viện của tôi');
});

test('verified users can start seller onboarding from my library', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->assertSee('Trở thành người bán')
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
        ->assertSee('Mở bảng điều khiển')
        ->assertSee(route('seller.dashboard.index'));
});

test('seller dashboard redirects unverified users back to profile verification', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/seller/dashboard')
        ->assertRedirect('/my-profile?section=security')
        ->assertSessionHas('profile-status', 'Vui lòng xác minh email trước để mở đăng ký người bán.');
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
        ->assertSee('Chi tiết biến thể')
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
        ->assertDontSee('Xác nhận đã nhận')
        ->assertDontSee('Mở khiếu nại')
        ->call('confirmReceived', $orderItem->id)
        ->assertSee('Vui lòng mở key một lần trước khi xác nhận đã nhận.');

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
        ->assertDontSee('Mở khiếu nại')
        ->call('openComplaintForm', $orderItem->id)
        ->assertSet('complaintOrderItemId', null)
        ->assertSee('Chỉ có thể mở khiếu nại cho sản phẩm đã giao hoặc đang khiếu nại.');
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
        ->assertSee('Đánh dấu đơn này là hoàn tất?')
        ->call('confirmReceived', $orderItem->id)
        ->assertSet('confirmReceivedOrderItemId', null)
        ->assertSee('Viết đánh giá');

    expect($orderItem->refresh()->status)->toBe(OrderStatus::Completed);
});

test('confirming a seller item releases escrow to the seller wallet', function (): void {
    $user = User::factory()->create();
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'Library Seller',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    $order = Order::factory()->forBuyer($user)->create([
        'payment_status' => PaymentStatus::Completed,
    ]);
    $listing = createAdminListingForLibrary();
    $listing->forceFill(['seller_id' => $seller->id])->save();

    $orderItem = OrderItem::query()->create([
        'order_id'              => $order->id,
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'product_name_snapshot' => 'Seller Game Key',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 149000,
        'subtotal'              => 149000,
        'platform_fee'          => 14900,
        'seller_amount'         => 134100,
        'status'                => OrderStatus::Delivered,
    ]);

    $escrow = Escrow::query()->create([
        'order_item_id' => $orderItem->id,
        'seller_id'     => $seller->id,
        'amount'        => 134100,
        'release_date'  => now()->addDays(3),
        'status'        => EscrowStatus::Holding,
    ]);

    $sellerWallet = Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'LIBSELLER001',
        'balance'   => 0,
        'holding'   => 134100,
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
        ->call('confirmReceived', $orderItem->id)
        ->assertSet('confirmReceivedOrderItemId', null)
        ->assertSee('Sản phẩm trong đơn đã được đánh dấu hoàn tất.');

    expect($orderItem->fresh()->status)->toBe(OrderStatus::Completed)
        ->and($escrow->fresh()->status)->toBe(EscrowStatus::Released)
        ->and($sellerWallet->fresh()->balance)->toBe('134100.00')
        ->and($sellerWallet->fresh()->holding)->toBe('0.00')
        ->and(InternalWalletEntry::query()->where('type', InternalWalletEntryType::EscrowReleased)->count())->toBe(1);
});

test('authenticated user can leave a review for a completed order item', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->forBuyer($user)->create([
        'payment_status' => PaymentStatus::Completed,
    ]);
    $listing = createAdminListingForLibrary();

    Storage::fake(config('filesystems.public_disk'));

    $reviewImage = UploadedFile::fake()->create('review-proof.png', 120, 'image/png');
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
        ->assertSee('Viết đánh giá')
        ->call('openReviewForm', $orderItem->id)
        ->assertSet('reviewOrderItemId', $orderItem->id)
        ->set('reviewRating', '4')
        ->set('reviewComment', 'Fast delivery and the key worked right away.')
        ->set('reviewMedia', [$reviewImage, $reviewDocument])
        ->call('submitReview')
        ->assertHasNoErrors()
        ->assertSet('reviewOrderItemId', null)
        ->assertSee('Đánh giá của bạn đã được gửi.')
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
        ->assertSee('Đánh giá của bạn đã được gửi.');

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->set('reviewOrderItemId', $orderItem->id)
        ->set('reviewRating', '1')
        ->set('reviewComment', 'Trying to edit the review.')
        ->call('submitReview')
        ->assertHasNoErrors()
        ->assertSee('Bạn đã đánh giá sản phẩm này.');

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
        ->assertSee('Hội thoại khiếu nại')
        ->set('replyMessage', 'I have another screenshot showing the mismatch.')
        ->set('replyAttachments', [$replyFile])
        ->call('reply')
        ->assertHasNoErrors()
        ->assertSee('Tin nhắn của bạn đã được thêm vào hội thoại khiếu nại.');

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
