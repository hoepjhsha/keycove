<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Table\Complaint;

use App\Enums\ComplaintStatus;
use App\Livewire\Admin\Action\Complaint\ComplaintIndex;
use App\Models\Complaint;
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
            ->with([
                'orderItem.order.buyer',
                'orderItem.listing.seller.user',
                'messages',
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
            ->add('seller_name', fn (Complaint $model) => $model->orderItem?->listing?->seller?->user?->username ?? 'Shop Admin')
            ->add('reason')
            ->add('message_count', fn (Complaint $model) => $model->messages()->count())
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
            Column::make('Order Code', 'order_code', 'orderItem.order.order_code')->sortable()->searchable(),
            Column::make('Buyer', 'buyer_name', 'orderItem.order.buyer.username')->sortable()->searchable(),
            Column::make('Seller', 'seller_name', 'orderItem.listing.seller.user.username')->sortable()->searchable(),
            Column::make('Reason', 'reason')->sortable()->searchable(),
            Column::make('Messages', 'message_count')->sortable(),
            Column::make('Status', 'status_label', 'status')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
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
                    'x-tooltip' => 'View Details',
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
