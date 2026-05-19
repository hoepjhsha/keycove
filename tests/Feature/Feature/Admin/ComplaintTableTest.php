<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\ProductListingStatus;
use App\Livewire\Admin\Table\Complaint\ComplaintTable;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\User;
use Livewire\Livewire;

it('sorts admin complaints by order code', function (): void {
    $admin = User::factory()->admin()->create();
    $buyer = User::factory()->create();

    createComplaintTableFixture($buyer, 'ORD-ZZZ-001');
    createComplaintTableFixture($buyer, 'ORD-AAA-001');

    Livewire::actingAs($admin, 'admin')
        ->test(ComplaintTable::class)
        ->assertOk()
        ->set('sortField', 'orderItem.order.order_code')
        ->set('sortDirection', 'asc')
        ->assertSeeInOrder(['ORD-AAA-001', 'ORD-ZZZ-001']);
});

it('sorts admin complaints by message count', function (): void {
    $admin = User::factory()->admin()->create();
    $buyer = User::factory()->create();

    $complaintWithMoreMessages = createComplaintTableFixture($buyer, 'ORD-MSG-002');
    $complaintWithFewerMessages = createComplaintTableFixture($buyer, 'ORD-MSG-001');

    $complaintWithMoreMessages->messages()->createMany([
        ['sender_id' => $buyer->id, 'message' => 'First message', 'attachments' => null],
        ['sender_id' => $buyer->id, 'message' => 'Second message', 'attachments' => null],
    ]);

    $complaintWithFewerMessages->messages()->create([
        'sender_id'   => $buyer->id,
        'message'     => 'Only message',
        'attachments' => null,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(ComplaintTable::class)
        ->assertOk()
        ->set('sortField', 'messages_count')
        ->set('sortDirection', 'asc')
        ->assertSeeInOrder(['ORD-MSG-001', 'ORD-MSG-002']);
});

function createComplaintTableFixture(User $buyer, string $orderCode): Complaint
{
    $variant = ProductVariant::factory()->create();

    $listing = ProductListing::query()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Admin listing '.$orderCode,
        'slug'         => 'admin-listing-'.strtolower($orderCode),
        'price'        => 99000,
        'stock_count'  => 10,
        'status'       => ProductListingStatus::Active,
    ]);

    $order = Order::factory()->forBuyer($buyer)->create([
        'order_code'  => $orderCode,
        'total_price' => 99000,
    ]);

    $orderItem = $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'order_item_code'       => 'ITEM-'.strtolower($orderCode),
        'product_name_snapshot' => 'Complaint Test Product',
        'variant_snapshot'      => ['variant_id' => $variant->id],
        'quantity'              => 1,
        'unit_price'            => 99000,
        'subtotal'              => 99000,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Disputing,
    ]);

    return Complaint::factory()->forOrderItem($orderItem)->create();
}
