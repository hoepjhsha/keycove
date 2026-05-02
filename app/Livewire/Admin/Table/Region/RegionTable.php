<?php

namespace App\Livewire\Admin\Table\Region;

use App\Enums\GeneralStatus;
use App\Livewire\Admin\Action\Region\RegionIndex;
use App\Models\Region;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class RegionTable extends PowerGridComponent
{
    public string $tableName = 'regionTable';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

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
        return Region::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('parent', fn (Region $model) => $model->parent() ? $model->parent?->name : '')
            ->add('name')
            ->add('slug')
            ->add('flag_code')
            ->add('status_label', function (Region $model) {
                $status = $model->status;

                $labelText = method_exists($status, 'label') ? $status->label() : $status->name;

                $colorClass = match ($status) {
                    GeneralStatus::Active   => 'bg-green-500/10 text-green-500',
                    GeneralStatus::Inactive => 'bg-gray-500/10 text-gray-500',
                    GeneralStatus::Hidden   => 'bg-yellow-500/10 text-yellow-500',
                    GeneralStatus::Deleted  => 'bg-red-500/10 text-red-500',
                    default                 => 'bg-primary-500/10 text-primary-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('created_at_formatted', fn (Region $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('updated_at_formatted', fn (Region $model) => Carbon::parse($model->updated_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'id')
                ->index(),
            Column::make(__('admin.common.parent'), 'parent', 'parent_id'),
            Column::make(__('admin.common.name'), 'name')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.slug'), 'slug')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.flag_code'), 'flag_code')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.status'), 'status_label', 'status')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.created_at_short'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make(__('admin.common.updated_at_short'), 'updated_at_formatted', 'updated_at')
                ->sortable(),

            Column::action(__('admin.common.action')),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('slug')->operators(['contains']),

            Filter::multiSelect('parent_id', 'parent_id')
                ->dataSource(Region::whereNull('parent_id')->get())
                ->optionValue('id')
                ->optionLabel('name'),

            Filter::multiSelect('status', 'status')
                ->dataSource(collect(GeneralStatus::cases())->map(fn ($status) => [
                    'id'   => $status->value,
                    'name' => $status->label(),
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
        return [
            Button::add('create')
                ->slot(__('admin.common.create'))
                ->class('bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded text-sm transition-colors duration-200')
                ->dispatch('openCreateModal', []),

            Button::add('bulk-delete')
                ->slot(__('admin.common.bulk_delete').' (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('bg-transparent hover:bg-red-500 text-red-700 font-semibold hover:text-white py-2 px-4 border border-red-500 hover:border-transparent rounded text-sm ml-2 transition-colors duration-200')
                ->dispatch('bulkDelete', []),

            Button::add('bulk-status')
                ->slot(__('admin.common.change_status').' (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('bg-transparent hover:bg-yellow-500 text-yellow-700 font-semibold hover:text-white py-2 px-4 border border-yellow-500 hover:border-transparent rounded text-sm ml-2 transition-colors duration-200')
                ->dispatch('triggerBulkStatus', []),
        ];
    }

    public function actions(Region $row): array
    {
        $deleteClass = $row->status === GeneralStatus::Deleted ? 'hidden' : '';

        return [
            Button::add('view')
                ->slot('<i class="fa-regular fa-eye"></i>')
                ->id()
                ->class('text-indigo-600 hover:text-indigo-900 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => __('admin.common.view_details'),
                ])
                ->dispatch('viewRegion', ['rowId' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa-solid fa-pen-to-square"></i>')
                ->id()
                ->class('text-blue-600 hover:text-blue-800 px-1 py-1 transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => __('admin.common.edit'),
                ])
                ->dispatch('editRegion', ['rowId' => $row->id]),

            Button::add('toggle-status')
                ->slot($row->status === GeneralStatus::Active
                    ? '<i class="fa-solid fa-circle-xmark text-red-400 hover:text-red-700"></i>'
                    : '<i class="fa-solid fa-circle-check text-green-500 hover:text-green-800"></i>')
                ->id()
                ->class('px-1 py-1 transition-all hover:scale-110 text-lg '.$deleteClass)
                ->attributes([
                    'x-tooltip' => $row->status === GeneralStatus::Active ? __('admin.common.deactivate_now') : __('admin.common.activate_now'),
                ])
                ->dispatch('toggleStatus', ['rowId' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa-solid fa-trash-can"></i>')
                ->id()
                ->class('text-red-500 hover:text-red-700 px-1 py-1 transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => __('admin.common.delete'),
                ])
                ->dispatch('deleteRegion', ['rowId' => $row->id]),

            Button::add('revertDelete')
                ->slot('<i class="fa-solid fa-rotate-left"></i>')
                ->id()
                ->class('text-yellow-500 hover:text-yellow-700 px-1 py-1 transition-all hover:scale-110 '.($row->status === GeneralStatus::Deleted ? '' : 'hidden'))
                ->attributes([
                    'x-tooltip' => __('admin.common.restore'),
                ])
                ->dispatch('revertDelete', ['rowId' => $row->id]),
        ];
    }

    #[On('toggleStatus')]
    public function toggleStatus($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_change_status'),
            'text'   => __('admin.swal.change_status_text', ['name' => __('admin.nav.regions')]),
            'method' => 'performToggleStatus',
            'id'     => $rowId,
        ]);
    }

    #[On('performToggleStatus')]
    public function performToggleStatus($id): void
    {
        $region = Region::findOrFail($id);
        $region->status = match ($region->status) {
            GeneralStatus::Inactive => GeneralStatus::Active,
            default                 => GeneralStatus::Inactive,
        };
        $region->save();

        $this->dispatch('swal:success', ['message' => __('admin.messages.status_changed', ['Name' => __('admin.nav.regions')])]);
    }

    #[On('deleteRegion')]
    public function deleteRegion($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_delete', ['name' => __('admin.nav.regions')]),
            'text'   => __('admin.swal.delete_text_irreversible', ['name' => __('admin.nav.regions')]),
            'method' => 'performDelete',
            'id'     => $rowId,
        ]);
    }

    #[On('performDelete')]
    public function performDelete($id): void
    {
        DB::transaction(function () use ($id) {
            $region = Region::findOrFail($id);

            $region->status = GeneralStatus::Deleted;
            $region->save();

            $region->delete();
        });

        $this->dispatch('swal:success', ['message' => __('admin.messages.deleted', ['Name' => __('admin.nav.regions')])]);
    }

    #[On('revertDelete')]
    public function revertDelete($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_restore', ['name' => __('admin.nav.regions')]),
            'text'   => __('admin.swal.restore_text_irreversible', ['name' => __('admin.nav.regions')]),
            'method' => 'performRevertDelete',
            'id'     => $rowId,
        ]);
    }

    #[On('performRevertDelete')]
    public function performRevertDelete($id): void
    {
        $region = Region::withTrashed()->findOrFail($id);

        $region->restore();

        $region->status = GeneralStatus::Inactive;
        $region->save();

        $this->dispatch('swal:success', ['message' => __('admin.messages.restored', ['Name' => __('admin.nav.regions')])]);
    }

    #[On('bulkDelete')]
    public function bulkDelete(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => __('admin.messages.select_at_least_one', ['name' => __('admin.nav.regions')])]);

            return;
        }

        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.delete_selected', ['count' => count($this->checkboxValues)]),
            'method' => 'performBulkDelete',
            'id'     => null,
        ]);
    }

    #[On('performBulkDelete')]
    public function performBulkDelete(): void
    {
        $alreadyDeletedExists = Region::onlyTrashed()
            ->whereIn('id', $this->checkboxValues)
            ->exists();

        if ($alreadyDeletedExists) {
            $this->dispatch('swal:error', [
                'message' => __('admin.messages.cannot_change_deleted_status'),
            ]);

            return;
        }

        DB::transaction(function () {

            Region::whereIn('id', $this->checkboxValues)
                ->update([
                    'status' => GeneralStatus::Deleted,
                ]);

            Region::whereIn('id', $this->checkboxValues)->delete();
        });

        $this->dispatch('swal:success', ['message' => __('admin.messages.bulk_delete_completed')]);
    }

    #[On('triggerBulkStatus')]
    public function triggerBulkStatus(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => __('admin.messages.select_at_least_one', ['name' => __('admin.nav.regions')])]);

            return;
        }

        $this->dispatch('openBulkStatusModal', ids: $this->checkboxValues)->to(RegionIndex::class);
    }
}
