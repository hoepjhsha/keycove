<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manage Orders')]
class OrderIndex extends Component
{
    public bool $showViewModal = false;

    public ?array $viewData = null;

    public function render()
    {
        return view('pages.admin.order.index')->layout('components.layouts.dashboard');
    }

    #[On('openViewModal')]
    public function viewOrder($id): void
    {
        $order = Order::with(['buyer', 'items', 'transaction'])->find($id);

        if ($order) {
            $statusColorClass = match ($order->status) {
                OrderStatus::PendingPayment => 'bg-yellow-500/10 text-yellow-500',
                OrderStatus::Processing => 'bg-blue-500/10 text-blue-500',
                OrderStatus::Delivered => 'bg-purple-500/10 text-purple-500',
                OrderStatus::Disputing => 'bg-orange-500/10 text-orange-500',
                OrderStatus::Completed => 'bg-green-500/10 text-green-500',
                OrderStatus::Cancelled => 'bg-red-500/10 text-red-500',
                OrderStatus::Refunded => 'bg-red-500/10 text-red-500',
                default => 'bg-gray-500/10 text-gray-500',
            };

            $statusLabel = method_exists($order->status, 'label')
                ? $order->status->label()
                : $order->status->name;
            $statusBadge = '<span class="'.$statusColorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$statusLabel.'</span>';

            $paymentColorClass = match ($order->payment_method) {
                PaymentMethod::VNPay => 'bg-indigo-500/10 text-indigo-500',
                PaymentMethod::Stripe => 'bg-blue-500/10 text-blue-500',
                default => 'bg-gray-500/10 text-gray-500',
            };

            $paymentLabel = method_exists($order->payment_method, 'label')
                ? $order->payment_method->label()
                : $order->payment_method->name;
            $paymentBadge = '<span class="'.$paymentColorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$paymentLabel.'</span>';

            $this->viewData = [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'buyer_username' => $order->buyer?->username ?? '-',
                'buyer_email' => $order->buyer?->email ?? '-',
                'total_price' => number_format((float) $order->total_price, 2).' VND',
                'status_badge' => $statusBadge,
                'payment_badge' => $paymentBadge,
                'created_at' => $order->created_at->format('d/m/Y H:i:s'),
                'updated_at' => $order->updated_at->format('d/m/Y H:i:s'),
                'items_count' => $order->items->count(),
                'order_url' => route('admin.orders.detail', $order->id),
            ];

            $this->showViewModal = true;
        }
    }
}
