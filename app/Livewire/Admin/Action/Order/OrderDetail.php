<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Order;

use App\Models\Order;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Order Details')]
class OrderDetail extends Component
{
    public Order $order;

    public function mount($id): void
    {
        $this->order = Order::with(['buyer', 'items.listing.seller.user'])->findOrFail($id);
    }

    public function render()
    {
        return view('pages.admin.order.detail')->layout('components.layouts.dashboard');
    }
}
