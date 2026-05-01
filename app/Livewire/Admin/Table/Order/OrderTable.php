<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Table\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Livewire\Admin\Action\Order\OrderIndex;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class OrderTable extends PowerGridComponent
{
    public string $tableName = 'orderTable';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(perPage: 25)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Order::query()
            ->select('orders.*')
            ->selectSub(
                OrderItem::query()
                    ->selectRaw(
                        'CASE
                            WHEN SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) > 0 THEN ?
                            WHEN SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) > 0 THEN ?
                            WHEN SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) > 0 THEN ?
                            WHEN SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) > 0 THEN ?
                            WHEN SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) > 0 THEN ?
                            WHEN SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) > 0 THEN ?
                            ELSE ?
                        END',
                        [
                            OrderStatus::PendingPayment->value,
                            OrderStatus::PendingPayment->value,
                            OrderStatus::Processing->value,
                            OrderStatus::Processing->value,
                            OrderStatus::Disputing->value,
                            OrderStatus::Disputing->value,
                            OrderStatus::Cancelled->value,
                            OrderStatus::Cancelled->value,
                            OrderStatus::Refunded->value,
                            OrderStatus::Refunded->value,
                            OrderStatus::Delivered->value,
                            OrderStatus::Delivered->value,
                            OrderStatus::Completed->value,
                        ]
                    )
                    ->whereColumn('order_items.order_id', 'orders.id'),
                'aggregated_status'
            )
            ->with(['buyer', 'items', 'paymentTransactions']);
    }

    public function relationSearch(): array
    {
        return [
            'buyer' => ['username', 'email'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('order_code')
            ->add('buyer_name', fn (Order $model) => $model->buyer?->username ?? '-')
            ->add('buyer_email', fn (Order $model) => $model->buyer?->email ?? '-')
            ->add('total_price_formatted', fn (Order $model) => number_format((float) $model->total_price, 2).' VND')
            ->add('status_label', function (Order $model) {
                $status = $model->status;
                $labelText = method_exists($status, 'label') ? $status->label() : $status->name;

                $colorClass = match ($status) {
                    OrderStatus::PendingPayment => 'bg-yellow-500/10 text-yellow-500',
                    OrderStatus::Processing     => 'bg-blue-500/10 text-blue-500',
                    OrderStatus::Delivered      => 'bg-purple-500/10 text-purple-500',
                    OrderStatus::Disputing      => 'bg-orange-500/10 text-orange-500',
                    OrderStatus::Completed      => 'bg-green-500/10 text-green-500',
                    OrderStatus::Cancelled      => 'bg-red-500/10 text-red-500',
                    OrderStatus::Refunded       => 'bg-red-500/10 text-red-500',
                    default                     => 'bg-gray-500/10 text-gray-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('payment_method_label', function (Order $model) {
                $method = $model->payment_method;
                $labelText = method_exists($method, 'label') ? $method->label() : $method->name;

                $colorClass = match ($method) {
                    PaymentMethod::VNPay  => 'bg-indigo-500/10 text-indigo-500',
                    PaymentMethod::Stripe => 'bg-blue-500/10 text-blue-500',
                    default               => 'bg-gray-500/10 text-gray-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('payment_status_label', function (Order $model) {
                $status = $model->payment_status;
                $labelText = method_exists($status, 'label') ? $status->label() : $status->name;

                $colorClass = match ($status) {
                    PaymentStatus::Pending   => 'bg-yellow-500/10 text-yellow-500',
                    PaymentStatus::Completed => 'bg-green-500/10 text-green-500',
                    PaymentStatus::Failed    => 'bg-red-500/10 text-red-500',
                    PaymentStatus::Cancelled => 'bg-gray-500/10 text-gray-500',
                    PaymentStatus::Refunded  => 'bg-orange-500/10 text-orange-500',
                    default                  => 'bg-gray-500/10 text-gray-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('payment_transactions_count', fn (Order $model) => $model->paymentTransactions->count())
            ->add('created_at_formatted', fn (Order $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('updated_at_formatted', fn (Order $model) => Carbon::parse($model->updated_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'id')->index(),
            Column::make(__('admin.common.order_code'), 'order_code')->sortable()->searchable(),
            Column::make(__('admin.common.buyer'), 'buyer_name', 'buyer.username')->sortable()->searchable(),
            Column::make(__('admin.common.total'), 'total_price_formatted', 'total_price')->sortable()->bodyAttribute('text-right'),
            Column::make(__('admin.common.status'), 'status_label', 'aggregated_status')->sortable(),
            Column::make(__('admin.common.payment'), 'payment_method_label', 'payment_method')->sortable(),
            Column::make(__('admin.common.payment_status'), 'payment_status_label', 'payment_status')->sortable(),
            Column::make(__('admin.common.created_at_short'), 'created_at_formatted', 'created_at')->sortable(),
            Column::action(__('admin.common.action')),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('order_code')->operators(['contains']),
            Filter::inputText('buyer.username', 'username')->operators(['contains']),
            Filter::inputText('buyer.email', 'email')->operators(['contains']),

            Filter::number('total_price')
                ->thousands('.')
                ->decimal(','),

            Filter::multiSelect('payment_method', 'payment_method')
                ->dataSource(collect(PaymentMethod::cases())->map(fn ($method) => [
                    'id'   => $method->value,
                    'name' => method_exists($method, 'label') ? $method->label() : $method->name,
                ]))
                ->optionValue('id')
                ->optionLabel('name'),

            // TODO: Datepicker disabled - need to fix date range
            //             Filter::datepicker('created_at_formatted', 'created_at'),
            // TODO: Datepicker disabled - need to fix date range
            //             Filter::datepicker('updated_at_formatted', 'updated_at'),
        ];
    }

    public function header(): array
    {
        return [];
    }

    public function actions(Order $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa-regular fa-eye"></i>')
                ->id()
                ->class('text-indigo-600 hover:text-indigo-900 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => __('admin.common.view_details'),
                ])
                ->dispatch('viewOrder', ['rowId' => $row->id]),
        ];
    }

    #[On('viewOrder')]
    public function viewOrder($rowId): void
    {
        $this->dispatch('openViewModal', id: $rowId)->to(OrderIndex::class);
    }
}
