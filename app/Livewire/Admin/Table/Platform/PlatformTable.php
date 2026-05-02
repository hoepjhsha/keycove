<?php

namespace App\Livewire\Admin\Table\Platform;

use App\Enums\GeneralStatus;
use App\Livewire\Admin\Action\Platform\PlatformIndex;
use App\Models\Platform;
use App\Utilities\StorageUtility;
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

final class PlatformTable extends PowerGridComponent
{
    public string $tableName = 'platformTable';

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
        return Platform::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('slug')
            ->add('icon_path')
            ->add('icon_display', function (Platform $model) {
                $iconUrl = StorageUtility::getUrl($model->icon_path);
                $escapedPath = e($model->icon_path ?? '');
                $escapedName = e($model->name ?? 'Platform icon');

                if ($iconUrl) {
                    return '<img src="'.e($iconUrl).'" alt="'.$escapedName.' icon" class="w-8 h-8 object-contain" loading="lazy" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                }

                return '<img src="" alt="" class="hidden w-8 h-8 object-contain"><div class="w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded flex items-center justify-center text-slate-400"><i class="fa-solid fa-image text-xs"></i></div>';
            })
            ->add('base_url', fn (Platform $model) => '<a href="'.$model->base_url.'" target="_blank" class="text-blue-600 underline hover:text-blue-800 hover:no-underline transition-colors">'.$model->base_url.'</a>')
            ->add('status_label', function (Platform $model) {
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
            ->add('created_at_formatted', fn (Platform $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('updated_at_formatted', fn (Platform $model) => Carbon::parse($model->updated_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'id')
                ->index(),
            Column::make(__('admin.common.name'), 'name')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.slug'), 'slug')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.icon'), 'icon_display', 'icon_path')
                ->sortable()
                ->searchable(),

            Column::make(__('admin.common.base_url'), 'base_url')
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

    public function actions(Platform $row): array
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
                ->dispatch('viewPlatform', ['rowId' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa-solid fa-pen-to-square"></i>')
                ->id()
                ->class('text-blue-600 hover:text-blue-800 px-1 py-1 transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => __('admin.common.edit'),
                ])
                ->dispatch('editPlatform', ['rowId' => $row->id]),

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
                ->dispatch('deletePlatform', ['rowId' => $row->id]),

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
            'text'   => __('admin.swal.change_status_text', ['name' => __('admin.nav.platforms')]),
            'method' => 'performToggleStatus',
            'id'     => $rowId,
        ]);
    }

    #[On('performToggleStatus')]
    public function performToggleStatus($id): void
    {
        $platform = Platform::findOrFail($id);
        $platform->status = match ($platform->status) {
            GeneralStatus::Inactive => GeneralStatus::Active,
            default                 => GeneralStatus::Inactive,
        };
        $platform->save();

        $this->dispatch('swal:success', ['message' => __('admin.messages.status_changed', ['Name' => __('admin.nav.platforms')])]);
    }

    #[On('deletePlatform')]
    public function deletePlatform($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_delete', ['name' => __('admin.nav.platforms')]),
            'text'   => __('admin.swal.delete_text_irreversible', ['name' => __('admin.nav.platforms')]),
            'method' => 'performDelete',
            'id'     => $rowId,
        ]);
    }

    #[On('performDelete')]
    public function performDelete($id): void
    {
        DB::transaction(function () use ($id) {
            $platform = Platform::findOrFail($id);

            $platform->status = GeneralStatus::Deleted;
            $platform->save();

            $platform->delete();
        });

        $this->dispatch('swal:success', ['message' => __('admin.messages.deleted', ['Name' => __('admin.nav.platforms')])]);
    }

    #[On('revertDelete')]
    public function revertDelete($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_restore', ['name' => __('admin.nav.platforms')]),
            'text'   => __('admin.swal.restore_text_irreversible', ['name' => __('admin.nav.platforms')]),
            'method' => 'performRevertDelete',
            'id'     => $rowId,
        ]);
    }

    #[On('performRevertDelete')]
    public function performRevertDelete($id): void
    {
        $platform = Platform::withTrashed()->findOrFail($id);

        $platform->restore();

        $platform->status = GeneralStatus::Inactive;
        $platform->save();

        $this->dispatch('swal:success', ['message' => __('admin.messages.restored', ['Name' => __('admin.nav.platforms')])]);
    }

    #[On('bulkDelete')]
    public function bulkDelete(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => __('admin.messages.select_at_least_one', ['name' => __('admin.nav.platforms')])]);

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
        $alreadyDeletedExists = Platform::onlyTrashed()
            ->whereIn('id', $this->checkboxValues)
            ->exists();

        if ($alreadyDeletedExists) {
            $this->dispatch('swal:error', [
                'message' => __('admin.messages.cannot_change_deleted_status'),
            ]);

            return;
        }

        DB::transaction(function () {

            Platform::whereIn('id', $this->checkboxValues)
                ->update([
                    'status' => GeneralStatus::Deleted,
                ]);

            Platform::whereIn('id', $this->checkboxValues)->delete();
        });

        $this->dispatch('swal:success', ['message' => __('admin.messages.bulk_delete_completed')]);
    }

    #[On('triggerBulkStatus')]
    public function triggerBulkStatus(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => __('admin.messages.select_at_least_one', ['name' => __('admin.nav.platforms')])]);

            return;
        }

        $this->dispatch('openBulkStatusModal', ids: $this->checkboxValues)->to(PlatformIndex::class);
    }
}
