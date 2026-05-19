<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Livewire\Shop\Checkout\CheckoutReview;
use App\Livewire\Shop\Library\MyLibrary;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OperatingSystem;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\User;
use App\Services\Payment\VNPayGateway;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('creates an order from cart and redirects to vnpay', function (): void {
    $user = User::factory()->create();
    $listing = activeCheckoutListing();
    ProductKey::factory()->withListing($listing)->available()->count(2)->create();
    $otherListing = activeCheckoutListing();
    ProductKey::factory()->withListing($otherListing)->available()->count(2)->create();

    $cart = Cart::factory()->forUser($user)->create();
    $selectedCartItem = CartItem::factory()->forCart($cart)->withListing($listing)->create(['quantity' => 1]);
    $otherCartItem = CartItem::factory()->forCart($cart)->withListing($otherListing)->create(['quantity' => 1]);

    $this->actingAs($user)
        ->post(route('app.cart.checkout'), ['item_codes' => [$selectedCartItem->cart_item_code]])
        ->assertRedirect();

    $order = Order::query()->where('buyer_id', $user->id)->first();
    $order?->load('transactions.wallet');

    expect($order)->not->toBeNull();
    expect($order?->payment_method)->toBe(PaymentMethod::VNPay);
    expect($order?->payment_status)->toBe(PaymentStatus::Pending);
    expect(PaymentTransaction::query()->where('order_id', $order?->id)->exists())->toBeTrue();
    expect($order?->transactions)->toHaveCount(0);
    expect(ProductKey::query()->where('listing_id', $listing->id)->where('status', ProductKeyStatus::Reserved)->count())->toBe(1);
    expect($listing->fresh()->stock_count)->toBe(5);
    expect(CartItem::query()->whereKey($otherCartItem->id)->exists())->toBeTrue();
});

it('prevents sellers from checking out their own listings', function (): void {
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'Checkout Seller',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => 1,
        'kyc_rejected_reason' => null,
    ]);

    $listing = activeCheckoutListing();
    $listing->forceFill(['seller_id' => $seller->id])->save();
    ProductKey::factory()->withListing($listing)->available()->count(1)->create();

    $cart = Cart::factory()->forUser($sellerUser)->create();
    $cartItem = CartItem::factory()->forCart($cart)->withListing($listing)->create(['quantity' => 1]);

    $this->actingAs($sellerUser)
        ->post(route('app.cart.checkout'), ['item_codes' => [$cartItem->cart_item_code]])
        ->assertStatus(422);

    expect(Order::query()->where('buyer_id', $sellerUser->id)->exists())->toBeFalse();
});

it('does not apply commission to platform-owned listings during checkout', function (): void {
    $user = User::factory()->create();
    $listing = activeCheckoutListing();
    $listing->forceFill(['seller_id' => null])->save();
    ProductKey::factory()->withListing($listing)->available()->count(1)->create();

    $cart = Cart::factory()->forUser($user)->create();
    $cartItem = CartItem::factory()->forCart($cart)->withListing($listing)->create(['quantity' => 1]);

    $this->actingAs($user)
        ->post(route('app.cart.checkout'), ['item_codes' => [$cartItem->cart_item_code]])
        ->assertRedirect();

    $orderItem = Order::query()
        ->where('buyer_id', $user->id)
        ->firstOrFail()
        ->items()
        ->firstOrFail();

    expect($orderItem->seller_id)->toBeNull()
        ->and($orderItem->platform_fee)->toBe('0.00')
        ->and($orderItem->seller_amount)->toBe('199000.00')
        ->and($orderItem->escrow)->toBeNull();
});

it('applies commission and creates escrow for seller-owned listings during checkout', function (): void {
    $buyer = User::factory()->create();
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'Checkout Seller Escrow',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    $listing = activeCheckoutListing();
    $listing->forceFill(['seller_id' => $seller->id])->save();
    ProductKey::factory()->withListing($listing)->available()->count(1)->create();

    $cart = Cart::factory()->forUser($buyer)->create();
    $cartItem = CartItem::factory()->forCart($cart)->withListing($listing)->create(['quantity' => 1]);

    $this->actingAs($buyer)
        ->post(route('app.cart.checkout'), ['item_codes' => [$cartItem->cart_item_code]])
        ->assertRedirect();

    $orderItem = Order::query()
        ->where('buyer_id', $buyer->id)
        ->firstOrFail()
        ->items()
        ->with('escrow')
        ->firstOrFail();

    expect($orderItem->seller_id)->toBe($seller->id)
        ->and($orderItem->platform_fee)->toBe('19900.00')
        ->and($orderItem->seller_amount)->toBe('179100.00')
        ->and($orderItem->escrow)->not->toBeNull()
        ->and($orderItem->escrow?->amount)->toBe('179100.00');
});

it('shows a checkout review page for selected cart items', function (): void {
    $user = User::factory()->create();
    $listing = activeCheckoutListing();
    ProductKey::factory()->withListing($listing)->available()->count(2)->create();

    $cart = Cart::factory()->forUser($user)->create();
    $cartItem = CartItem::factory()->forCart($cart)->withListing($listing)->create(['quantity' => 2]);

    $response = $this->actingAs($user)->get('/checkout?item_codes='.$cartItem->cart_item_code);

    $response->assertOk();
    $response->assertSeeLivewire(CheckoutReview::class);
    $response->assertSee('Kiểm tra đơn hàng');
    $response->assertSee('Kiểm tra thanh toán');
    $response->assertSee($listing->display_name);
    $response->assertSee('Xác nhận');
});

it('settles paid orders when vnpay returns success', function (): void {
    $user = User::factory()->create();
    $listing = activeCheckoutListing();
    $order = Order::factory()->forBuyer($user)->create([
        'payment_method' => PaymentMethod::VNPay,
        'payment_status' => PaymentStatus::Pending,
        'total_price'    => 199000,
    ]);

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => $listing->seller_id,
        'order_item_code'       => 'OI-20260429-ABCDE1',
        'product_name_snapshot' => 'Test listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 0,
        'seller_amount'         => 199000,
        'status'                => OrderStatus::PendingPayment,
    ]);

    ProductKey::factory()->withListing($listing)->reserved()->create([
        'order_item_id' => $orderItem->id,
    ]);

    PaymentTransaction::factory()->completed()->create([
        'order_id'               => $order->id,
        'gateway'                => PaymentMethod::VNPay,
        'gateway_transaction_id' => $order->order_code,
    ]);

    $request = [
        'vnp_TxnRef'       => $order->order_code,
        'vnp_Amount'       => 19900000,
        'vnp_ResponseCode' => '00',
        'vnp_SecureHash'   => hash_hmac('sha512', http_build_query([
            'vnp_Amount'       => 19900000,
            'vnp_ResponseCode' => '00',
            'vnp_TxnRef'       => $order->order_code,
        ]), config('services.payment.vnpay.hash_secret', '')),
    ];

    app(VNPayGateway::class)->handleIpn($request);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Completed);
    expect($listing->fresh()->stock_count)->toBe(4);
});

it('cancels a pending order and releases reserved keys', function (): void {
    $user = User::factory()->create();
    $listing = activeCheckoutListing();

    $order = Order::factory()->forBuyer($user)->create([
        'payment_method' => PaymentMethod::VNPay,
        'payment_status' => PaymentStatus::Pending,
        'total_price'    => 199000,
    ]);

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => $listing->seller_id,
        'order_item_code'       => 'OI-20260429-CANCEL',
        'product_name_snapshot' => 'Checkout Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 0,
        'seller_amount'         => 199000,
        'status'                => OrderStatus::PendingPayment,
    ]);

    $reservedKey = ProductKey::factory()->withListing($listing)->reserved()->create([
        'order_item_id' => $orderItem->id,
    ]);

    PaymentTransaction::factory()->create([
        'order_id'               => $order->id,
        'gateway'                => PaymentMethod::VNPay,
        'gateway_transaction_id' => $order->order_code,
        'status'                 => PaymentStatus::Pending,
        'amount'                 => 199000,
    ]);

    Livewire::actingAs($user)
        ->test(MyLibrary::class)
        ->call('cancelOrder', $order->id)
        ->assertHasNoErrors();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Cancelled)
        ->and($orderItem->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($reservedKey->fresh()->status)->toBe(ProductKeyStatus::Available)
        ->and($reservedKey->fresh()->order_item_id)->toBeNull()
        ->and($listing->fresh()->stock_count)->toBe(5);
});

it('expires pending orders after 24 hours and releases reserved keys', function (): void {
    $user = User::factory()->create();
    $listing = activeCheckoutListing();

    $order = Order::factory()->forBuyer($user)->create([
        'payment_method' => PaymentMethod::VNPay,
        'payment_status' => PaymentStatus::Pending,
        'total_price'    => 199000,
    ]);

    $order->forceFill([
        'created_at' => now()->subDay()->subHour(),
    ])->save();

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => $listing->seller_id,
        'order_item_code'       => 'OI-20260429-EXPIRE',
        'product_name_snapshot' => 'Checkout Listing',
        'variant_snapshot'      => ['variant_id' => $listing->variant_id],
        'quantity'              => 1,
        'unit_price'            => 199000,
        'subtotal'              => 199000,
        'platform_fee'          => 0,
        'seller_amount'         => 199000,
        'status'                => OrderStatus::PendingPayment,
    ]);

    $reservedKey = ProductKey::factory()->withListing($listing)->reserved()->create([
        'order_item_id' => $orderItem->id,
    ]);

    PaymentTransaction::factory()->create([
        'order_id'               => $order->id,
        'gateway'                => PaymentMethod::VNPay,
        'gateway_transaction_id' => $order->order_code,
        'status'                 => PaymentStatus::Pending,
        'amount'                 => 199000,
    ]);

    $this->artisan('app:expire-pending-orders')->assertSuccessful();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Cancelled)
        ->and($orderItem->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($reservedKey->fresh()->status)->toBe(ProductKeyStatus::Available)
        ->and($listing->fresh()->stock_count)->toBe(5);
});

function activeCheckoutListing(): ProductListing
{
    $region = Region::factory()->create([
        'status'    => GeneralStatus::Active,
        'slug'      => Str::uuid()->toString(),
        'flag_code' => Str::substr(Str::uuid()->toString(), 0, 10),
    ]);

    $platform = Platform::factory()->create([
        'status' => GeneralStatus::Active,
        'slug'   => Str::uuid()->toString(),
    ]);

    $os = OperatingSystem::factory()->create([
        'status' => GeneralStatus::Active,
        'slug'   => Str::uuid()->toString(),
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

    return ProductListing::factory()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Checkout Listing',
        'price'        => 199000,
        'stock_count'  => 5,
        'status'       => ProductListingStatus::Active,
    ]);
}
