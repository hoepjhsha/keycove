<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Table\Product;

use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Livewire\Admin\Action\Product\ProductDetail;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class ProductVariantsTable extends PowerGridComponent
{
    public string $tableName = 'productVariantsTable';

    public int $productId;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public array $expandedRows = [];

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
        return ProductVariant::query()
            ->where('product_id', $this->productId)
            ->with(['region', 'platform', 'operatingSystem'])
            ->withCount(['listings' => function ($query) {
                $query->where('status', '!=', ProductListingStatus::Deleted);
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
            ->add('region_name', fn (ProductVariant $model) => $model->region->name ?? '-')
            ->add('region_flag', fn (ProductVariant $model) => $model->region->flag_code ?? '')
            ->add('platform_name', fn (ProductVariant $model) => $model->platform->name ?? '-')
            ->add('platform_icon', fn (ProductVariant $model) => $model->platform->icon ?? '')
            ->add('os_name', fn (ProductVariant $model) => $model->operatingSystem->name ?? '-')
            ->add('os_icon', fn (ProductVariant $model) => $model->operatingSystem->icon ?? '')
            ->add('edition')
            ->add('status_label', fn (ProductVariant $model) => $this->getStatusLabel($model->status))
            ->add('listings_count')
            ->add('created_at')
            ->add('updated_at');
    }

    protected function getStatusLabel(ProductVariantStatus $status): string
    {
        $colorClass = match ($status) {
            ProductVariantStatus::Draft        => 'bg-gray-500/10 text-gray-500',
            ProductVariantStatus::Active       => 'bg-green-500/10 text-green-500',
            ProductVariantStatus::Hidden       => 'bg-yellow-500/10 text-yellow-500',
            ProductVariantStatus::Discontinued => 'bg-red-500/10 text-red-500',
            ProductVariantStatus::Deleted      => 'bg-red-500/10 text-red-500',
            default                            => 'bg-slate-500/10 text-slate-500',
        };

        $labelText = $status->label();

        return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'id')
                ->index(),

            Column::make(__('admin.common.region'), 'region_name')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.nav.platforms'), 'platform_name')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.os'), 'os_name')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.edition'), 'edition')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.status'), 'status_label', 'status')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.listings'), 'listings_count')
                ->sortable(),

            Column::action(__('admin.common.action')),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('edition')->operators(['contains']),
            Filter::multiSelect('status', 'status')
                ->dataSource(collect(ProductVariantStatus::cases())->map(fn ($status) => [
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
                ->class('bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded text-sm transition-colors duration-200')
                ->dispatch('openVariantModal', []),

            Button::add('bulk-delete')
                ->slot(__('admin.common.bulk_delete').' (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('bg-transparent hover:bg-red-500 text-red-700 font-semibold hover:text-white py-2 px-4 border border-red-500 hover:border-transparent rounded text-sm ml-2 transition-colors duration-200')
                ->dispatch('bulkDeleteVariant', []),

            Button::add('bulk-status')
                ->slot(__('admin.common.change_status').' (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('bg-transparent hover:bg-yellow-500 text-yellow-700 font-semibold hover:text-white py-2 px-4 border border-yellow-500 hover:border-transparent rounded text-sm ml-2 transition-colors duration-200')
                ->dispatch('triggerBulkStatusVariant', []),
        ];
    }

    public function actions(ProductVariant $row): array
    {
        $deleteClass = $row->status === ProductVariantStatus::Deleted ? 'hidden' : '';

        return [
            Button::add('toggle-status')
                ->slot($row->status === ProductVariantStatus::Active
                    ? '<i class="fa-solid fa-circle-xmark text-red-400 hover:text-red-700"></i>'
                    : '<i class="fa-solid fa-circle-check text-green-500 hover:text-green-800"></i>')
                ->id()
                ->class('px-1 py-1 transition-all hover:scale-110 text-lg '.$deleteClass)
                ->attributes([
                    'x-tooltip' => $row->status === ProductVariantStatus::Active ? __('admin.common.deactivate_now') : __('admin.common.activate_now'),
                ])
                ->dispatch('toggleVariantStatus', ['rowId' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa-solid fa-pen-to-square"></i>')
                ->id()
                ->class('text-blue-600 hover:text-blue-800 px-1 py-1 transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => __('admin.common.edit'),
                ])
                ->dispatch('openVariantModal', ['variantId' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa-solid fa-trash-can"></i>')
                ->id()
                ->class('text-red-500 hover:text-red-700 px-1 py-1 transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => __('admin.common.delete'),
                ])
                ->dispatch('deleteVariant', ['rowId' => $row->id]),

            Button::add('revertDelete')
                ->slot('<i class="fa-solid fa-rotate-left"></i>')
                ->id()
                ->class('text-yellow-500 hover:text-yellow-700 px-1 py-1 transition-all hover:scale-110 '.($row->status === ProductVariantStatus::Deleted ? '' : 'hidden'))
                ->attributes([
                    'x-tooltip' => __('admin.common.restore'),
                ])
                ->dispatch('restoreVariant', ['rowId' => $row->id]),
        ];
    }

    public function toggleRow(int $variantId): void
    {
        if (in_array($variantId, $this->expandedRows)) {
            $this->expandedRows = array_values(array_filter($this->expandedRows, fn ($id) => $id !== $variantId));
        } else {
            $this->expandedRows[] = $variantId;
        }
    }

    #[On('toggleVariantStatus')]
    public function toggleVariantStatus($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_change_status'),
            'text'   => __('admin.swal.change_status_text', ['name' => __('admin.common.variant')]),
            'method' => 'performToggleVariantStatus',
            'id'     => $rowId,
        ]);
    }

    #[On('performToggleVariantStatus')]
    public function performToggleVariantStatus($id): void
    {
        $variant = ProductVariant::findOrFail($id);

        $newStatus = match ($variant->status) {
            ProductVariantStatus::Draft  => ProductVariantStatus::Active,
            ProductVariantStatus::Active => ProductVariantStatus::Hidden,
            ProductVariantStatus::Hidden => ProductVariantStatus::Draft,
            default                      => ProductVariantStatus::Draft,
        };

        $variant->status = $newStatus;
        $variant->save();

        $this->dispatch('swal:success', ['message' => __('admin.messages.status_changed', ['Name' => __('admin.common.variant')])]);
    }

    #[On('deleteVariant')]
    public function deleteVariant($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_delete', ['name' => __('admin.common.variant')]),
            'text'   => __('admin.swal.delete_text_irreversible', ['name' => __('admin.common.variant')]),
            'method' => 'performDeleteVariant',
            'id'     => $rowId,
        ]);
    }

    #[On('performDeleteVariant')]
    public function performDeleteVariant($id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $variant = ProductVariant::findOrFail($id);

                if ($variant->listings()->where('status', '!=', ProductListingStatus::Deleted)->exists()) {
                    throw new \Exception(__('admin.validation.variant_delete_active_listings'));
                }

                $variant->status = ProductVariantStatus::Deleted;
                $variant->save();
                $variant->delete();
            });

            $this->dispatch('swal:success', ['message' => __('admin.messages.deleted', ['Name' => __('admin.common.variant')])]);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    #[On('restoreVariant')]
    public function restoreVariant($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_restore', ['name' => __('admin.common.variant')]),
            'text'   => __('admin.swal.restore_text', ['name' => __('admin.common.variant')]),
            'method' => 'performRestoreVariant',
            'id'     => $rowId,
        ]);
    }

    #[On('performRestoreVariant')]
    public function performRestoreVariant($id): void
    {
        $variant = ProductVariant::withTrashed()->findOrFail($id);
        $variant->restore();
        $variant->status = ProductVariantStatus::Draft;
        $variant->save();

        $this->dispatch('swal:success', ['message' => __('admin.messages.restored', ['Name' => __('admin.common.variant')])]);
    }

    #[On('bulkDeleteVariant')]
    public function bulkDeleteVariant(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => __('admin.messages.select_at_least_one', ['name' => __('admin.common.variant')])]);

            return;
        }

        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.delete_selected_named', ['count' => count($this->checkboxValues), 'name' => __('admin.common.variants')]),
            'method' => 'performBulkDeleteVariant',
            'id'     => null,
        ]);
    }

    #[On('performBulkDeleteVariant')]
    public function performBulkDeleteVariant(): void
    {
        try {
            DB::transaction(function () {
                $variants = ProductVariant::whereIn('id', $this->checkboxValues)->get();

                foreach ($variants as $variant) {
                    if ($variant->listings()->where('status', '!=', ProductListingStatus::Deleted)->exists()) {
                        continue;
                    }

                    $variant->status = ProductVariantStatus::Deleted;
                    $variant->save();
                    $variant->delete();
                }
            });

            $this->dispatch('swal:success', ['message' => __('admin.messages.bulk_delete_completed_short')]);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    #[On('triggerBulkStatusVariant')]
    public function triggerBulkStatusVariant(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => __('admin.messages.select_at_least_one', ['name' => __('admin.common.variant')])]);

            return;
        }

        $this->dispatch('openVariantBulkStatusModal', ids: $this->checkboxValues)->to(ProductDetail::class);
    }
}
