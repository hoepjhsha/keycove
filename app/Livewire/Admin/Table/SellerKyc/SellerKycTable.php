<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Table\SellerKyc;

use App\Enums\KycStatus;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class SellerKycTable extends PowerGridComponent
{
    public string $tableName = 'sellerKycTable';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Seller::query()->with('user')->orderByDesc('created_at');
    }

    public function relationSearch(): array
    {
        return [
            'user' => [
                'username',
                'email',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('user_info', fn (Seller $model) => '<div><p class="font-medium">'.$model->user?->username.'</p><p class="text-xs text-gray-500">'.$model->user?->email.'</p></div>')
            ->add('shop_name')
            ->add('kyc_status_label', function (Seller $model) {
                $status = $model->kyc_status;

                $labelText = $status->label();

                $colorClass = match ($status) {
                    KycStatus::Approved => 'bg-green-500/10 text-green-500',
                    KycStatus::Pending => 'bg-yellow-500/10 text-yellow-500',
                    KycStatus::Rejected => 'bg-red-500/10 text-red-500',
                    default => 'bg-primary-500/10 text-primary-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('created_at_formatted', fn (Seller $model) => $model->created_at ? Carbon::parse($model->created_at)->format('d/m/Y H:i:s') : 'N/A')
            ->add('updated_at_formatted', fn (Seller $model) => $model->updated_at ? Carbon::parse($model->updated_at)->format('d/m/Y H:i:s') : 'N/A');
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'id')
                ->index(),

            Column::make('User', 'user_info', 'user_id'),

            Column::make('Shop Name', 'shop_name')
                ->sortable()
                ->searchable(),

            Column::make('KYC Status', 'kyc_status_label', 'kyc_status')
                ->sortable(),

            Column::make('Applied At', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make('Updated At', 'updated_at_formatted', 'updated_at')
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('shop_name')->operators(['contains']),

            Filter::multiSelect('kyc_status', 'kyc_status')
                ->dataSource(collect(KycStatus::cases())->map(fn ($status) => [
                    'id' => $status->value,
                    'name' => $status->label(),
                ]))
                ->optionValue('id')
                ->optionLabel('name'),

            Filter::datepicker('created_at_formatted', 'created_at'),
            Filter::datepicker('updated_at_formatted', 'updated_at'),
        ];
    }

    public function header(): array
    {
        return [];
    }

    public function actions(Seller $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa-regular fa-eye"></i>')
                ->id()
                ->class('text-indigo-600 hover:text-indigo-900 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => 'View Details',
                ])
                ->dispatch('viewSellerKyc', ['rowId' => $row->id]),

            Button::add('process')
                ->slot('<i class="fa-solid fa-list-check"></i>')
                ->id()
                ->class('text-yellow-600 hover:text-yellow-800 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => 'Process KYC',
                ])
                ->dispatch('processSellerKyc', ['rowId' => $row->id]),
        ];
    }

    public function actionRules(Seller $row): array
    {
        return [
            Rule::button('process')
                ->when(fn (Seller $model) => $model->kyc_status !== KycStatus::Pending)
                ->hide(),
        ];
    }
}
