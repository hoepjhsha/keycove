<?php

namespace App\Livewire\Admin\Table\OperatingSystem;

use App\Enums\GeneralStatus;
use App\Livewire\Admin\Action\OperatingSystem\OperatingSystemIndex;
use App\Models\OperatingSystem;
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

final class OperatingSystemTable extends PowerGridComponent
{
    public string $tableName = 'operatingSystemTable';

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
        return OperatingSystem::query();
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
            ->add('status_label', function (OperatingSystem $model) {
                $status = $model->status;

                $labelText = method_exists($status, 'label') ? $status->label() : $status->name;

                $colorClass = match ($status) {
                    GeneralStatus::Active => 'bg-green-500/10 text-green-500',
                    GeneralStatus::Inactive => 'bg-gray-500/10 text-gray-500',
                    GeneralStatus::Hidden => 'bg-yellow-500/10 text-yellow-500',
                    GeneralStatus::Deleted => 'bg-red-500/10 text-red-500',
                    default => 'bg-primary-500/10 text-primary-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('created_at_formatted', fn (OperatingSystem $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('updated_at_formatted', fn (OperatingSystem $model) => Carbon::parse($model->updated_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'id')
                ->index(),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Slug', 'slug')
                ->sortable()
                ->searchable(),

            Column::make('Icon', 'icon_path')
                ->sortable()
                ->searchable(),

            Column::make('Status', 'status_label', 'status')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make('Updated at', 'updated_at_formatted', 'updated_at')
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('slug')->operators(['contains']),

            Filter::multiSelect('status', 'status')
                ->dataSource(collect(GeneralStatus::cases())->map(fn ($status) => [
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
        return [
            Button::add('create')
                ->slot('Create')
                ->class('bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded text-sm transition-colors duration-200')
                ->dispatch('openCreateModal', []),

            Button::add('bulk-delete')
                ->slot('Bulk Delete (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('bg-transparent hover:bg-red-500 text-red-700 font-semibold hover:text-white py-2 px-4 border border-red-500 hover:border-transparent rounded text-sm ml-2 transition-colors duration-200')
                ->dispatch('bulkDelete', []),

            Button::add('bulk-status')
                ->slot('Change Status (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('bg-transparent hover:bg-yellow-500 text-yellow-700 font-semibold hover:text-white py-2 px-4 border border-yellow-500 hover:border-transparent rounded text-sm ml-2 transition-colors duration-200')
                ->dispatch('triggerBulkStatus', []),
        ];
    }

    public function actions(OperatingSystem $row): array
    {
        $deleteClass = $row->status === GeneralStatus::Deleted ? 'hidden' : '';

        return [
            Button::add('view')
                ->slot('<i class="fa-regular fa-eye"></i>')
                ->id()
                ->class('text-indigo-600 hover:text-indigo-900 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => 'View Details',
                ])
                ->dispatch('viewOperatingSystem', ['rowId' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa-solid fa-pen-to-square"></i>')
                ->id()
                ->class('text-blue-600 hover:text-blue-800 px-1 py-1 transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => 'Edit OperatingSystem',
                ])
                ->dispatch('editOperatingSystem', ['rowId' => $row->id]),

            Button::add('toggle-status')
                ->slot($row->status === GeneralStatus::Active
                    ? '<i class="fa-solid fa-circle-xmark text-red-400 hover:text-red-700"></i>'
                    : '<i class="fa-solid fa-circle-check text-green-500 hover:text-green-800"></i>')
                ->id()
                ->class('px-1 py-1 transition-all hover:scale-110 text-lg '.$deleteClass)
                ->attributes([
                    'x-tooltip' => $row->status === GeneralStatus::Active ? 'Deactivate Now' : 'Activate Now',
                ])
                ->dispatch('toggleStatus', ['rowId' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa-solid fa-trash-can"></i>')
                ->id()
                ->class('text-red-500 hover:text-red-700 px-1 py-1 transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => 'Delete',
                ])
                ->dispatch('deleteOperatingSystem', ['rowId' => $row->id]),

            Button::add('revertDelete')
                ->slot('<i class="fa-solid fa-rotate-left"></i>')
                ->id()
                ->class('text-yellow-500 hover:text-yellow-700 px-1 py-1 transition-all hover:scale-110 '.($row->status === GeneralStatus::Deleted ? '' : 'hidden'))
                ->attributes([
                    'x-tooltip' => 'Restore',
                ])
                ->dispatch('revertDelete', ['rowId' => $row->id]),
        ];
    }

    #[On('toggleStatus')]
    public function toggleStatus($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title' => 'Change Status?',
            'text' => 'Are you sure you want to change the status of this operatingSystem?',
            'method' => 'performToggleStatus',
            'id' => $rowId,
        ]);
    }

    #[On('performToggleStatus')]
    public function performToggleStatus($id): void
    {
        $operatingSystem = OperatingSystem::findOrFail($id);
        $operatingSystem->status = match ($operatingSystem->status) {
            GeneralStatus::Inactive => GeneralStatus::Active,
            default => GeneralStatus::Inactive,
        };
        $operatingSystem->save();

        $this->dispatch('swal:success', ['message' => 'OperatingSystem Status Changed Successfully']);
    }

    #[On('deleteOperatingSystem')]
    public function deleteOperatingSystem($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title' => 'Delete OperatingSystem?',
            'text' => 'Are you sure you want to delete this operatingSystem? This action cannot be undone.',
            'method' => 'performDelete',
            'id' => $rowId,
        ]);
    }

    #[On('performDelete')]
    public function performDelete($id): void
    {
        DB::transaction(function () use ($id) {
            $operatingSystem = OperatingSystem::findOrFail($id);

            $operatingSystem->status = GeneralStatus::Deleted;
            $operatingSystem->save();

            $operatingSystem->delete();
        });

        $this->dispatch('swal:success', ['message' => 'OperatingSystem Deleted Successfully']);
    }

    #[On('revertDelete')]
    public function revertDelete($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title' => 'Restore OperatingSystem?',
            'text' => 'Are you sure you want to restore this operatingSystem? This action cannot be undone.',
            'method' => 'performRevertDelete',
            'id' => $rowId,
        ]);
    }

    #[On('performRevertDelete')]
    public function performRevertDelete($id): void
    {
        $operatingSystem = OperatingSystem::withTrashed()->findOrFail($id);

        $operatingSystem->restore();

        $operatingSystem->status = GeneralStatus::Inactive;
        $operatingSystem->save();

        $this->dispatch('swal:success', ['message' => 'OperatingSystem Restored Successfully']);
    }

    #[On('bulkDelete')]
    public function bulkDelete(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => 'Please select at least one operatingSystem!']);

            return;
        }

        $this->dispatch('swal:confirm', [
            'title' => 'Delete '.count($this->checkboxValues).' selected items?',
            'method' => 'performBulkDelete',
            'id' => null,
        ]);
    }

    #[On('performBulkDelete')]
    public function performBulkDelete(): void
    {
        $alreadyDeletedExists = OperatingSystem::onlyTrashed()
            ->whereIn('id', $this->checkboxValues)
            ->exists();

        if ($alreadyDeletedExists) {
            $this->dispatch('swal:error', [
                'message' => 'Some selected items are already deleted or in the trash.',
            ]);

            return;
        }

        DB::transaction(function () {

            OperatingSystem::whereIn('id', $this->checkboxValues)
                ->update([
                    'status' => GeneralStatus::Deleted,
                ]);

            OperatingSystem::whereIn('id', $this->checkboxValues)->delete();
        });

        $this->dispatch('swal:success', ['message' => 'Bulk delete completed successfully.']);
    }

    #[On('triggerBulkStatus')]
    public function triggerBulkStatus(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => 'Please select at least one operatingSystem!']);

            return;
        }

        $this->dispatch('openBulkStatusModal', ids: $this->checkboxValues)->to(OperatingSystemIndex::class);
    }
}
