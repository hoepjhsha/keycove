<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Table\Complaint;

use App\Enums\ComplaintStatus;
use App\Livewire\Admin\Action\Complaint\ComplaintIndex;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class ComplaintTable extends PowerGridComponent
{
    public string $tableName = 'complaintTable';

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
        return Complaint::query()
            ->withCount('messages')
            ->with([
                'orderItem.order.buyer',
                'orderItem.listing.seller.user',
            ]);
    }

    public function relationSearch(): array
    {
        return [
            'orderItem.order.buyer'         => ['username', 'email'],
            'orderItem.listing.seller.user' => ['username', 'email'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('complaint_id', fn (Complaint $model) => '#'.$model->id)
            ->add('order_code', fn (Complaint $model) => $model->orderItem?->order?->order_code ?? '-')
            ->add('buyer_name', fn (Complaint $model) => $model->orderItem?->order?->buyer?->username ?? '-')
            ->add('seller_name', fn (Complaint $model) => $model->orderItem?->listing?->seller?->user?->username ?? __('admin.common.shop_admin'))
            ->add('reason')
            ->add('message_count', fn (Complaint $model) => (int) $model->messages_count)
            ->add('status_label', function (Complaint $model) {
                $status = $model->status;
                $labelText = method_exists($status, 'label') ? $status->label() : $status->name;

                $colorClass = match ($status) {
                    ComplaintStatus::Open            => 'bg-blue-500/10 text-blue-500',
                    ComplaintStatus::InProcess       => 'bg-yellow-500/10 text-yellow-500',
                    ComplaintStatus::Escalated       => 'bg-orange-500/10 text-orange-500',
                    ComplaintStatus::ApprovedRefund  => 'bg-green-500/10 text-green-500',
                    ComplaintStatus::RejectedRelease => 'bg-gray-500/10 text-gray-500',
                    default                          => 'bg-gray-500/10 text-gray-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('created_at_formatted', fn (Complaint $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'complaint_id', 'id')->index(),
            Column::make(__('admin.common.order_code'), 'order_code', 'orderItem.order.order_code')
                ->sortUsing(fn (Builder $query, string $direction) => $query->orderBy(
                    Order::query()
                        ->select('orders.order_code')
                        ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                        ->whereColumn('order_items.id', 'complaints.order_item_id')
                        ->limit(1),
                    $direction
                ))
                ->searchable(),
            Column::make(__('admin.common.buyer'), 'buyer_name', 'orderItem.order.buyer.username')
                ->sortUsing(fn (Builder $query, string $direction) => $query->orderBy(
                    User::query()
                        ->select('users.username')
                        ->join('orders', 'users.id', '=', 'orders.buyer_id')
                        ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                        ->whereColumn('order_items.id', 'complaints.order_item_id')
                        ->limit(1),
                    $direction
                ))
                ->searchable(),
            Column::make(__('admin.common.seller'), 'seller_name', 'orderItem.listing.seller.user.username')
                ->sortUsing(fn (Builder $query, string $direction) => $query->orderBy(
                    User::query()
                        ->select('users.username')
                        ->join('sellers', 'users.id', '=', 'sellers.user_id')
                        ->join('order_items', 'sellers.id', '=', 'order_items.seller_id')
                        ->whereColumn('order_items.id', 'complaints.order_item_id')
                        ->limit(1),
                    $direction
                ))
                ->searchable(),
            Column::make(__('admin.common.reason'), 'reason')->sortable()->searchable(),
            Column::make(__('admin.common.messages'), 'message_count', 'messages_count')->sortable(),
            Column::make(__('admin.common.status'), 'status_label', 'status')->sortable(),
            Column::make(__('admin.common.created_at_short'), 'created_at_formatted', 'created_at')->sortable(),
            Column::action(__('admin.common.action')),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('reason')->operators(['contains']),
            Filter::inputText('orderItem.order.order_code', 'order_code')->operators(['contains']),
            Filter::inputText('orderItem.order.buyer.username', 'buyer_username')->operators(['contains']),
            Filter::inputText('orderItem.listing.seller.user.username', 'seller_username')->operators(['contains']),

            Filter::multiSelect('status', 'status')
                ->dataSource(collect(ComplaintStatus::cases())->map(fn ($status) => [
                    'id'   => $status->value,
                    'name' => method_exists($status, 'label') ? $status->label() : $status->name,
                ]))
                ->optionValue('id')
                ->optionLabel('name'),
        ];
    }

    public function header(): array
    {
        return [];
    }

    public function actions(Complaint $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa-regular fa-eye"></i>')
                ->id()
                ->class('text-indigo-600 hover:text-indigo-900 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => __('admin.common.view_details'),
                ])
                ->dispatch('viewComplaint', ['rowId' => $row->id]),
        ];
    }

    #[On('viewComplaint')]
    public function viewComplaint($rowId): void
    {
        $this->dispatch('openViewModal', id: $rowId)->to(ComplaintIndex::class);
    }
}
