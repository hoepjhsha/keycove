<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Table\InternalWallet;

use App\Enums\InternalWalletDirection;
use App\Enums\InternalWalletEntryType;
use App\Enums\TransactionStatus;
use App\Models\InternalWalletEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class InternalWalletTable extends PowerGridComponent
{
    public string $tableName = 'internalWalletTable';

    public string $sortField = 'occurred_at';

    public string $sortDirection = 'desc';

    public function setUp(): array
    {
        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()->showPerPage(perPage: 25)->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return InternalWalletEntry::query()
            ->leftJoin('orders', 'internal_wallet_entries.order_id', '=', 'orders.id')
            ->select('internal_wallet_entries.*', 'orders.order_code as order_code_raw')
            ->with(['order.buyer', 'wallet']);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('order_code', fn (InternalWalletEntry $model) => $model->order?->order_code ?? '-')
            ->add('type_label', function (InternalWalletEntry $model): string {
                $label = method_exists($model->type, 'label') ? $model->type->label() : $model->type->name;
                $class = match ($model->type) {
                    InternalWalletEntryType::PaymentReceived => 'bg-emerald-500/10 text-emerald-500',
                    InternalWalletEntryType::RefundPaid,
                    InternalWalletEntryType::SellerPayoutFailed => 'bg-rose-500/10 text-rose-500',
                    InternalWalletEntryType::SellerPayoutRequested,
                    InternalWalletEntryType::SellerPayoutCompleted => 'bg-indigo-500/10 text-indigo-500',
                    InternalWalletEntryType::EscrowHeld,
                    InternalWalletEntryType::EscrowReleased => 'bg-sky-500/10 text-sky-500',
                    default                                 => 'bg-slate-500/10 text-slate-500',
                };

                return '<span class="'.$class.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$label.'</span>';
            })
            ->add('direction_label', fn (InternalWalletEntry $model) => $model->direction->label())
            ->add('amount_formatted', function (InternalWalletEntry $model): string {
                $prefix = match ($model->direction) {
                    InternalWalletDirection::Inflow  => '+',
                    InternalWalletDirection::Outflow => '-',
                    default                          => '',
                };

                return $prefix.number_format((float) $model->amount, 2).' VND';
            })
            ->add('status_label', function (InternalWalletEntry $model): string {
                $label = method_exists($model->status, 'label') ? $model->status->label() : $model->status->name;
                $class = match ($model->status) {
                    TransactionStatus::Pending   => 'bg-amber-500/10 text-amber-500',
                    TransactionStatus::Completed => 'bg-emerald-500/10 text-emerald-500',
                    TransactionStatus::Failed    => 'bg-rose-500/10 text-rose-500',
                    TransactionStatus::Cancelled => 'bg-slate-500/10 text-slate-500',
                    default                      => 'bg-slate-500/10 text-slate-500',
                };

                return '<span class="'.$class.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$label.'</span>';
            })
            ->add('occurred_at_formatted', fn (InternalWalletEntry $model) => Carbon::parse($model->occurred_at)->format('d/m/Y H:i:s'))
            ->add('source_reference', fn (InternalWalletEntry $model) => $model->source_type !== null ? class_basename($model->source_type).' #'.$model->source_id : '-');
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'id')->index(),
            Column::make(__('admin.common.order_code'), 'order_code', 'order_id')->sortable()->searchable(),
            Column::make(__('admin.common.type'), 'type_label', 'type')->sortable(),
            Column::make(__('admin.common.direction'), 'direction_label', 'direction')->sortable(),
            Column::make(__('admin.common.amount'), 'amount_formatted', 'amount')->sortable()->bodyAttribute('text-right'),
            Column::make(__('admin.common.status'), 'status_label', 'status')->sortable(),
            Column::make(__('admin.common.source'), 'source_reference', 'source_id'),
            Column::make(__('admin.common.occurred_at'), 'occurred_at_formatted', 'occurred_at')->sortable(),
            Column::action(__('admin.common.action')),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('order_code', 'orders.order_code')->operators(['contains']),
            Filter::multiSelect('status', 'internal_wallet_entries.status')
                ->dataSource(collect(TransactionStatus::cases())->map(fn (TransactionStatus $status): array => [
                    'id'   => $status->value,
                    'name' => $status->label(),
                ]))
                ->optionValue('id')
                ->optionLabel('name'),
            Filter::multiSelect('type', 'internal_wallet_entries.type')
                ->dataSource(collect(InternalWalletEntryType::cases())->map(fn (InternalWalletEntryType $type): array => [
                    'id'   => $type->value,
                    'name' => $type->label(),
                ]))
                ->optionValue('id')
                ->optionLabel('name'),
            Filter::multiSelect('direction', 'internal_wallet_entries.direction')
                ->dataSource(collect(InternalWalletDirection::cases())->map(fn (InternalWalletDirection $direction): array => [
                    'id'   => $direction->value,
                    'name' => $direction->label(),
                ]))
                ->optionValue('id')
                ->optionLabel('name'),
        ];
    }

    public function actions(InternalWalletEntry $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa-regular fa-eye"></i>')
                ->id()
                ->class('text-indigo-600 hover:text-indigo-900 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => __('admin.common.view_details'),
                ])
                ->dispatch('viewInternalWalletEntryModal', ['rowId' => $row->id]),
        ];
    }
}
