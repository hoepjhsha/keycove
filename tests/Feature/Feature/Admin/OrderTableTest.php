<?php

declare(strict_types=1);

use App\Livewire\Admin\Table\Order\OrderTable;
use App\Models\Order;
use App\Models\User;
use Livewire\Livewire;

it('sorts admin orders by buyer username', function (): void {
    $admin = User::factory()->admin()->create();
    $buyerA = User::factory()->create(['username' => 'buyer-alpha']);
    $buyerB = User::factory()->create(['username' => 'buyer-zulu']);

    Order::factory()->forBuyer($buyerB)->create();
    Order::factory()->forBuyer($buyerA)->create();

    Livewire::actingAs($admin, 'admin')
        ->test(OrderTable::class)
        ->assertOk()
        ->set('sortField', 'buyer.username')
        ->set('sortDirection', 'asc')
        ->assertSeeInOrder(['buyer-alpha', 'buyer-zulu']);
});
