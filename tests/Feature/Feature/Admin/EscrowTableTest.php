<?php

declare(strict_types=1);

use App\Enums\AuditEvent;
use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductListingStatus;
use App\Livewire\Admin\Table\Escrow\EscrowTable;
use App\Models\AuditLog;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('asks for confirmation before unfreezing a frozen escrow', function (): void {
    $admin = User::factory()->admin()->create();
    $escrow = Escrow::factory()
        ->forOrderItem(createEscrowTableOrderItem())
        ->frozen()
        ->create();

    Livewire::actingAs($admin, 'admin')
        ->test(EscrowTable::class)
        ->call('unfreezeEscrow', $escrow->id)
        ->assertDispatched('swal:confirm');
});

it('unfreezes a frozen escrow and records an audit log', function (): void {
    $admin = User::factory()->admin()->create();
    $escrow = Escrow::factory()
        ->forOrderItem(createEscrowTableOrderItem())
        ->frozen()
        ->create();

    Livewire::actingAs($admin, 'admin')
        ->test(EscrowTable::class)
        ->call('performUnfreezeEscrow', $escrow->id)
        ->assertDispatched('swal:success');

    $auditLog = AuditLog::query()
        ->where('auditable_type', Escrow::class)
        ->where('auditable_id', $escrow->id)
        ->where('event', AuditEvent::EscrowUnfrozen->value)
        ->first();

    expect($auditLog)->not->toBeNull();

    expect($escrow->fresh()->status)->toBe(EscrowStatus::Holding)
        ->and($auditLog->user_id)->toBe($admin->id)
        ->and($auditLog->old_values['status'])->toBe(EscrowStatus::Frozen->name)
        ->and($auditLog->new_values['status'])->toBe(EscrowStatus::Holding->name);
});

it('does not unfreeze escrows unless they are frozen', function (EscrowStatus $status): void {
    $admin = User::factory()->admin()->create();
    $escrow = Escrow::factory()
        ->forOrderItem(createEscrowTableOrderItem())
        ->create(['status' => $status]);

    Livewire::actingAs($admin, 'admin')
        ->test(EscrowTable::class)
        ->call('performUnfreezeEscrow', $escrow->id)
        ->assertDispatched('swal:error');

    expect($escrow->fresh()->status)->toBe($status)
        ->and(AuditLog::query()
            ->where('auditable_type', Escrow::class)
            ->where('auditable_id', $escrow->id)
            ->where('event', AuditEvent::EscrowUnfrozen->value)
            ->exists())->toBeFalse();
})->with([
    'holding'  => EscrowStatus::Holding,
    'released' => EscrowStatus::Released,
    'refunded' => EscrowStatus::Refunded,
]);

function createEscrowTableOrderItem(): OrderItem
{
    $variant = ProductVariant::factory()->create();
    $unique = Str::lower(Str::random(8));

    $listing = ProductListing::query()->create([
        'variant_id'   => $variant->id,
        'seller_id'    => null,
        'display_name' => 'Escrow Table Test Listing '.$unique,
        'slug'         => 'escrow-table-test-listing-'.$unique,
        'price'        => 99000,
        'stock_count'  => 10,
        'status'       => ProductListingStatus::Active,
    ]);

    $order = Order::factory()->create([
        'order_code'  => 'ORD-ESCROW-'.$unique,
        'total_price' => 99000,
    ]);

    return $order->items()->create([
        'listing_id'            => $listing->id,
        'seller_id'             => null,
        'order_item_code'       => 'ITEM-ESCROW-'.$unique,
        'product_name_snapshot' => 'Escrow Table Test Product',
        'variant_snapshot'      => ['variant_id' => $variant->id],
        'quantity'              => 1,
        'unit_price'            => 99000,
        'subtotal'              => 99000,
        'platform_fee'          => 0,
        'seller_amount'         => 0,
        'status'                => OrderStatus::Processing,
    ]);
}
