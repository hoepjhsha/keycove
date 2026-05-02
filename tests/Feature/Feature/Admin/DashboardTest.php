<?php

declare(strict_types=1);

use App\Enums\AuditEvent;
use App\Enums\ComplaintStatus;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\WalletType;
use App\Enums\WithdrawStatus;
use App\Livewire\Admin\Action\Dashboard\DashboardIndex;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdraw;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('redirects guests away from the admin dashboard route', function (): void {
    $this->get('/admin/dashboard')
        ->assertRedirect('/admin/auth/login');
});

it('renders admin dashboard metrics and charts for authenticated admins', function (): void {
    Carbon::setTestNow('2026-05-02 10:00:00');

    $admin = User::factory()->admin()->create();
    $buyer = User::factory()->create();
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'     => $sellerUser->id,
        'shop_name'   => 'Keycove Seller',
        'cccd_number' => '123456789012',
        'kyc_status'  => KycStatus::Pending,
    ]);

    Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'SELLER-WALLET-01',
        'balance'   => 350000,
        'holding'   => 50000,
    ]);

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INTERNAL-WALLET-01',
        'balance'   => 150000,
        'holding'   => 0,
    ]);

    $product = Product::factory()->create([
        'submitted_by_seller_id' => $seller->id,
    ]);

    $variant = ProductVariant::factory()->withProduct($product)->create();

    $listing = ProductListing::factory()->withVariant($variant)->withSeller($seller)->create([
        'status' => ProductListingStatus::Active,
        'price'  => 120000,
    ]);

    ProductKey::factory()->count(3)->withListing($listing)->create([
        'status' => ProductKeyStatus::Available,
    ]);

    ProductListing::factory()->withVariant($variant)->withSeller($seller)->pending()->create();

    $order = Order::factory()->forBuyer($buyer)->create([
        'payment_status' => PaymentStatus::Completed,
        'total_price'    => 120000,
        'created_at'     => now()->subDays(1),
        'updated_at'     => now()->subDays(1),
    ]);

    $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'quantity'              => 1,
        'unit_price'            => 120000,
        'subtotal'              => 120000,
        'platform_fee'          => 12000,
        'seller_amount'         => 108000,
        'status'                => OrderStatus::Completed,
        'product_name_snapshot' => $product->name,
        'variant_snapshot'      => ['variant_id' => $variant->id],
        'created_at'            => now()->subDays(1),
        'updated_at'            => now()->subDays(1),
    ]);

    $complaintOrder = Order::factory()->forBuyer($buyer)->create([
        'payment_status' => PaymentStatus::Completed,
        'total_price'    => 99000,
        'created_at'     => now()->subDays(2),
        'updated_at'     => now()->subDays(2),
    ]);

    $complaintItem = $complaintOrder->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => $seller->id,
        'quantity'              => 1,
        'unit_price'            => 99000,
        'subtotal'              => 99000,
        'platform_fee'          => 9900,
        'seller_amount'         => 89100,
        'status'                => OrderStatus::Disputing,
        'product_name_snapshot' => $product->name,
        'variant_snapshot'      => ['variant_id' => $variant->id],
        'created_at'            => now()->subDays(2),
        'updated_at'            => now()->subDays(2),
    ]);

    Complaint::factory()->forOrderItem($complaintItem)->create([
        'complaint_code' => 'CMP-ADMIN-001',
        'status'         => ComplaintStatus::Open,
    ]);

    Review::query()->create([
        'user_id'       => $buyer->id,
        'order_item_id' => $order->items()->firstOrFail()->id,
        'rating'        => 4,
        'comment'       => 'Ổn định',
        'media'         => null,
    ]);

    Review::query()->create([
        'user_id'       => $buyer->id,
        'order_item_id' => $complaintItem->id,
        'rating'        => 5,
        'comment'       => 'Tốt',
        'media'         => null,
    ]);

    Withdraw::factory()->forWallet($seller->wallet)->create([
        'requested_by' => $sellerUser->id,
        'status'       => WithdrawStatus::Pending,
        'amount'       => 45000,
    ]);

    AuditLog::query()->create([
        'user_id'        => $admin->id,
        'auditable_type' => Order::class,
        'auditable_id'   => $order->id,
        'event'          => AuditEvent::EscrowReleased,
        'old_values'     => ['payment_status' => PaymentStatus::Pending->value],
        'new_values'     => ['payment_status' => PaymentStatus::Completed->value],
        'ip_address'     => '127.0.0.1',
        'user_agent'     => 'Pest',
        'created_at'     => now(),
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(DashboardIndex::class)
        ->assertSet('timeFilter', 'last_30_days')
        ->assertSee('Toàn cảnh tài chính, vận hành và tăng trưởng của KeyCove')
        ->assertSee('GMV')
        ->assertSee('219.000 VND')
        ->assertSee('Doanh thu sàn')
        ->assertSee('21.900 VND')
        ->assertSee('Nhật ký hệ thống')
        ->assertSee('5 khiếu nại cần xử lý gấp')
        ->assertSee('4,50/5')
        ->assertSee('CMP-ADMIN-001')
        ->assertSee('Keycove Seller');

    Carbon::setTestNow();
});
