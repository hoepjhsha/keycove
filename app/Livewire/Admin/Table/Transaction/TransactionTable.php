<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Table\Transaction;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class TransactionTable extends PowerGridComponent
{
    public string $tableName = 'transactionTable';

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
        return Transaction::query()
            ->join('orders', 'transactions.order_id', '=', 'orders.id')
            ->select('transactions.*', 'orders.order_code as order_code_raw')
            ->with(['order.buyer', 'wallet.seller']);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('order_code', fn (Transaction $model) => $model->order?->order_code ?? '-')
            ->add('type_label', function (Transaction $model) {
                $type = $model->type;
                $labelText = method_exists($type, 'label') ? $type->label() : $type->name;

                $colorClass = match ($type) {
                    TransactionType::Withdraw => 'bg-indigo-500/10 text-indigo-500',
                    TransactionType::Pay => 'bg-blue-500/10 text-blue-500',
                    TransactionType::Refund => 'bg-red-500/10 text-red-500',
                    default => 'bg-gray-500/10 text-gray-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('amount', function (Transaction $model) {
                $amountValue = $model->amount;

                if (empty($amountValue)) {
                    return '0.00 VND';
                }

                return $amountValue.' VND';
            })
            ->add('status_label', function (Transaction $model) {
                $status = $model->status;
                $labelText = method_exists($status, 'label') ? $status->label() : $status->name;

                $colorClass = match ($status) {
                    TransactionStatus::Pending => 'bg-yellow-500/10 text-yellow-500',
                    TransactionStatus::Completed => 'bg-green-500/10 text-green-500',
                    TransactionStatus::Failed => 'bg-red-500/10 text-red-500',
                    TransactionStatus::Cancelled => 'bg-gray-500/10 text-gray-500',
                    default => 'bg-primary-500/10 text-primary-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('created_at_formatted', fn (Transaction $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'id')->index(),
            Column::make('Order Code', 'order_code', 'order_id')->sortable()->searchable(),
            Column::make('Type', 'type_label', 'type')->sortable(),
            Column::make('Amount', 'amount')->sortable()->bodyAttribute('text-right'),
            Column::make('Status', 'status_label', 'status')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('order_code', 'orders.order_code')->operators(['contains']),

            Filter::multiSelect('status', 'transactions.status')
                ->dataSource(collect(TransactionStatus::cases())->map(fn ($status) => [
                    'id' => $status->value,
                    'name' => method_exists($status, 'label') ? $status->label() : $status->name,
                ]))
                ->optionValue('id')
                ->optionLabel('name'),

            Filter::number('amount', 'transactions.amount')
                ->thousands('.')
                ->decimal(','),

            Filter::multiSelect('type', 'transactions.type')
                ->dataSource(collect(TransactionType::cases())->map(fn ($type) => [
                    'id' => $type->value,
                    'name' => method_exists($type, 'label') ? $type->label() : $type->name,
                ]))
                ->optionValue('id')
                ->optionLabel('name'),

            // TODO: Datepicker disabled - need to fix date range
            // Filter::datepicker('created_at_formatted', 'transactions.created_at'),
        ];
    }

    public function actions(Transaction $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa-regular fa-eye"></i>')
                ->id()
                ->class('text-indigo-600 hover:text-indigo-900 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => 'View Details',
                ])
                ->dispatch('viewTransactionModal', ['rowId' => $row->id]),
        ];
    }
}
