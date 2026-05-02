<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Table\Product;

use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Livewire\Admin\Action\Product\ProductDetail;
use App\Models\ProductListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class ProductListingsTable extends PowerGridComponent
{
    public string $tableName = 'productListingsTable';

    public int $variantId;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public static function position(): int
    {
        return 1;
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSoftDeletes()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return ProductListing::query()
            ->where('variant_id', $this->variantId)
            ->with(['seller.user'])
            ->withCount(['keys' => function ($query) {
                $query->where('status', ProductKeyStatus::Available->value);
            }]);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('seller_name', fn (ProductListing $model) => $model->seller?->shop_name ?? __('admin.common.shop_admin'))
            ->add('seller_email', fn (ProductListing $model) => $model->seller?->user?->email ?? 'KeyCove')
            ->add('price_formatted', fn (ProductListing $model) => number_format((float) $model->price, 2).' VND')
            ->add('price', fn (ProductListing $model) => (float) $model->price)
            ->add('status_label', fn (ProductListing $model) => $this->getStatusLabel($model->status))
            ->add('keys_count')
            ->add('created_at')
            ->add('updated_at');
    }

    protected function getStatusLabel(ProductListingStatus $status): string
    {
        $colorClass = match ($status) {
            ProductListingStatus::Draft    => 'bg-gray-500/10 text-gray-500',
            ProductListingStatus::Pending  => 'bg-blue-500/10 text-blue-500',
            ProductListingStatus::Active   => 'bg-green-500/10 text-green-500',
            ProductListingStatus::Hidden   => 'bg-yellow-500/10 text-yellow-500',
            ProductListingStatus::Rejected => 'bg-red-500/10 text-red-500',
            ProductListingStatus::Closed   => 'bg-orange-500/10 text-orange-500',
            ProductListingStatus::Deleted  => 'bg-red-500/10 text-red-500',
            default                        => 'bg-slate-500/10 text-slate-500',
        };

        $labelText = $status->label();

        return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
    }

    public function columns(): array
    {
        return [
            Column::make(__('admin.common.id'), 'id')
                ->sortable(),

            Column::make(__('admin.common.seller'), 'seller_name')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.email'), 'seller_email'),

            Column::make(__('admin.common.price'), 'price_formatted', 'price')
                ->sortable(),

            Column::make(__('admin.common.status'), 'status_label', 'status')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.available_keys'), 'keys_count')
                ->sortable(),

            Column::action(__('admin.common.action')),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('seller_name')->operators(['contains']),
            Filter::multiSelect('status', 'status')
                ->dataSource(collect(ProductListingStatus::cases())->map(fn ($status) => [
                    'id'   => $status->value,
                    'name' => $status->label(),
                ]))
                ->optionValue('id')
                ->optionLabel('name'),
        ];
    }

    public function header(): array
    {
        return [
            Button::add('create')
                ->slot(__('admin.common.create'))
                ->class('bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-3 border border-blue-500 hover:border-transparent rounded text-xs transition-colors duration-200')
                ->dispatch('openListingModal', ['variantId' => $this->variantId]),

            Button::add('bulk-delete')
                ->slot(__('admin.common.bulk_delete').' (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('bg-transparent hover:bg-red-500 text-red-700 font-semibold hover:text-white py-1 px-3 border border-red-500 hover:border-transparent rounded text-xs ml-1 transition-colors duration-200')
                ->dispatch('bulkDeleteListing', []),

            Button::add('bulk-status')
                ->slot(__('admin.common.change_status').' (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('bg-transparent hover:bg-yellow-500 text-yellow-700 font-semibold hover:text-white py-1 px-3 border border-yellow-500 hover:border-transparent rounded text-xs ml-1 transition-colors duration-200')
                ->dispatch('triggerBulkStatusListing', []),
        ];
    }

    public function actions(ProductListing $row): array
    {
        $deleteClass = $row->status === ProductListingStatus::Deleted ? 'hidden' : '';

        return [
            Button::add('view-keys')
                ->slot('<i class="fa-solid fa-key"></i>')
                ->id()
                ->class('text-indigo-600 hover:text-indigo-900 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => __('admin.common.available_keys'),
                ])
                ->dispatch('openKeysModal', ['listingId' => $row->id]),

            Button::add('toggle-status')
                ->slot($row->status === ProductListingStatus::Active
                    ? '<i class="fa-solid fa-circle-xmark text-red-400 hover:text-red-700"></i>'
                    : '<i class="fa-solid fa-circle-check text-green-500 hover:text-green-800"></i>')
                ->id()
                ->class('px-1 py-1 transition-all hover:scale-110 text-lg '.$deleteClass)
                ->attributes([
                    'x-tooltip' => $row->status === ProductListingStatus::Active ? __('admin.common.deactivate_now') : __('admin.common.activate_now'),
                ])
                ->dispatch('toggleListingStatus', ['rowId' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa-solid fa-pen-to-square"></i>')
                ->id()
                ->class('text-blue-600 hover:text-blue-800 px-1 py-1 transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => __('admin.common.edit'),
                ])
                ->dispatch('openListingModal', ['variantId' => $this->variantId, 'listingId' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa-solid fa-trash-can"></i>')
                ->id()
                ->class('text-red-500 hover:text-red-700 px-1 py-1 transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => __('admin.common.delete'),
                ])
                ->dispatch('deleteListing', ['rowId' => $row->id]),

            Button::add('revertDelete')
                ->slot('<i class="fa-solid fa-rotate-left"></i>')
                ->id()
                ->class('text-yellow-500 hover:text-yellow-700 px-1 py-1 transition-all hover:scale-110 '.($row->status === ProductListingStatus::Deleted ? '' : 'hidden'))
                ->attributes([
                    'x-tooltip' => __('admin.common.restore'),
                ])
                ->dispatch('restoreListing', ['rowId' => $row->id]),
        ];
    }

    #[On('toggleListingStatus')]
    public function toggleListingStatus($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_change_status'),
            'text'   => __('admin.swal.change_status_text', ['name' => __('admin.common.listing')]),
            'method' => 'performToggleListingStatus',
            'id'     => $rowId,
        ]);
    }

    #[On('performToggleListingStatus')]
    public function performToggleListingStatus($id): void
    {
        $listing = ProductListing::findOrFail($id);

        $newStatus = match ($listing->status) {
            ProductListingStatus::Draft   => ProductListingStatus::Active,
            ProductListingStatus::Active  => ProductListingStatus::Hidden,
            ProductListingStatus::Hidden  => ProductListingStatus::Draft,
            ProductListingStatus::Pending => ProductListingStatus::Active,
            default                       => ProductListingStatus::Draft,
        };

        $listing->status = $newStatus;
        $listing->save();

        $this->dispatch('swal:success', ['message' => __('admin.messages.status_changed', ['Name' => __('admin.common.listing')])]);
    }

    #[On('deleteListing')]
    public function deleteListing($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_delete', ['name' => __('admin.common.listing')]),
            'text'   => __('admin.swal.delete_text_irreversible', ['name' => __('admin.common.listing')]),
            'method' => 'performDeleteListing',
            'id'     => $rowId,
        ]);
    }

    #[On('performDeleteListing')]
    public function performDeleteListing($id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $listing = ProductListing::findOrFail($id);

                $hasSoldKeys = $listing->keys()->where('status', ProductKeyStatus::Sold->value)->exists();

                if ($hasSoldKeys) {
                    throw new \Exception(__('admin.validation.listing_delete_sold_keys'));
                }

                $listing->status = ProductListingStatus::Deleted;
                $listing->save();
                $listing->delete();
            });

            $this->dispatch('swal:success', ['message' => __('admin.messages.deleted', ['Name' => __('admin.common.listing')])]);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    #[On('restoreListing')]
    public function restoreListing($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_restore', ['name' => __('admin.common.listing')]),
            'text'   => __('admin.swal.restore_text', ['name' => __('admin.common.listing')]),
            'method' => 'performRestoreListing',
            'id'     => $rowId,
        ]);
    }

    #[On('performRestoreListing')]
    public function performRestoreListing($id): void
    {
        $listing = ProductListing::withTrashed()->findOrFail($id);
        $listing->restore();
        $listing->status = ProductListingStatus::Draft;
        $listing->save();

        $this->dispatch('swal:success', ['message' => __('admin.messages.restored', ['Name' => __('admin.common.listing')])]);
    }

    #[On('bulkDeleteListing')]
    public function bulkDeleteListing(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => __('admin.messages.select_at_least_one', ['name' => __('admin.common.listing')])]);

            return;
        }

        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.delete_selected_named', ['count' => count($this->checkboxValues), 'name' => __('admin.common.listings')]),
            'method' => 'performBulkDeleteListing',
            'id'     => null,
        ]);
    }

    #[On('performBulkDeleteListing')]
    public function performBulkDeleteListing(): void
    {
        try {
            DB::transaction(function () {
                $listings = ProductListing::whereIn('id', $this->checkboxValues)->get();

                foreach ($listings as $listing) {
                    $hasSoldKeys = $listing->keys()->where('status', ProductKeyStatus::Sold->value)->exists();

                    if ($hasSoldKeys) {
                        continue;
                    }

                    $listing->status = ProductListingStatus::Deleted;
                    $listing->save();
                    $listing->delete();
                }
            });

            $this->dispatch('swal:success', ['message' => __('admin.messages.bulk_delete_completed_short')]);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    #[On('triggerBulkStatusListing')]
    public function triggerBulkStatusListing(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => __('admin.messages.select_at_least_one', ['name' => __('admin.common.listing')])]);

            return;
        }

        $this->dispatch('openListingBulkStatusModal', ids: $this->checkboxValues)->to(ProductDetail::class);
    }
}
