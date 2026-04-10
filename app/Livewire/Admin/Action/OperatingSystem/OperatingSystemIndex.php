<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\OperatingSystem;

use App\Enums\GeneralStatus;
use App\Livewire\Admin\Form\OperatingSystem\OperatingSystemBulkChangeStatusForm;
use App\Livewire\Admin\Form\OperatingSystem\OperatingSystemCreateForm;
use App\Livewire\Admin\Form\OperatingSystem\OperatingSystemEditForm;
use App\Models\OperatingSystem;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manage OperatingSystems')]
class OperatingSystemIndex extends Component
{
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showViewModal = false;

    public bool $showBulkStatusModal = false;

    public ?array $viewData = null;

    public array $bulkSelectedIds = [];

    public OperatingSystemCreateForm $createForm;

    public OperatingSystemEditForm $editForm;

    public OperatingSystemBulkChangeStatusForm $bulkChangeStatusForm;

    public function createOperatingSystem(): void
    {
        $result = $this->createForm->store();
        if ($result) {
            $this->reset('createForm');
            sweetalert()->title('Success!')->showConfirmButton(false)->success('OperatingSystem created successfully');
            $this->dispatch('pg:eventRefresh-operatingSystemTable');
        } else {
            sweetalert()->title('Error!')->showConfirmButton(false)->error('Failed to create operatingSystem');
        }

        $this->showCreateModal = false;
    }

    public function updateOperatingSystem(): void
    {
        $result = $this->editForm->update();
        if ($result) {
            sweetalert()->title('Success!')->showConfirmButton(false)->success('OperatingSystem updated successfully');
            $this->dispatch('pg:eventRefresh-operatingSystemTable');
        } else {
            sweetalert()->error('Failed to update operatingSystem');
        }
        $this->showEditModal = false;
    }

    public function bulkChangeStatusOperatingSystem(): void
    {
        if (OperatingSystem::withTrashed()
            ->whereIn('id', $this->bulkSelectedIds)
            ->where('status', GeneralStatus::Deleted)
            ->exists()
        ) {
            sweetalert()->title('Error!')->showConfirmButton(false)->error('Cannot change status of deleted items.');
            $this->showBulkStatusModal = false;

            return;
        }

        $result = $this->bulkChangeStatusForm->setStatus($this->bulkSelectedIds);

        if ($result) {
            sweetalert()->title('Success!')->showConfirmButton(false)->success('Status updated successfully for selected items.');

            $this->bulkChangeStatusForm->reset();
            $this->bulkSelectedIds = [];

            $this->dispatch('pg:eventRefresh-operatingSystemTable');
        } else {
            sweetalert()->title('Error!')->showConfirmButton(false)->error('Failed to update status.');
        }

        $this->showBulkStatusModal = false;
    }

    public function render()
    {
        return view('pages.admin.operating-system.index')->layout('components.layouts.dashboard');
    }

    #[On('openCreateModal')]
    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    #[On('viewOperatingSystem')]
    public function viewOperatingSystem($rowId): void
    {
        $operatingSystem = OperatingSystem::find($rowId);

        if ($operatingSystem) {
            $colorClass = match ($operatingSystem->status) {
                GeneralStatus::Active => 'bg-green-500/10 text-green-500',
                GeneralStatus::Inactive => 'bg-gray-500/10 text-gray-500',
                GeneralStatus::Hidden => 'bg-yellow-500/10 text-yellow-500',
                GeneralStatus::Deleted => 'bg-red-500/10 text-red-500',
                default => 'bg-primary-500/10 text-primary-500',
            };

            $statusLabel = '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$operatingSystem->status->label().'</span>';

            $this->viewData = [
                'id' => $operatingSystem->id,
                'name' => $operatingSystem->name,
                'slug' => $operatingSystem->slug,
                'icon_path' => $operatingSystem->icon_path,
                'status_label' => $statusLabel,
                'created_at' => $operatingSystem->created_at->format('d/m/Y H:i:s'),
                'updated_at' => $operatingSystem->updated_at->format('d/m/Y H:i:s'),
                'deleted_at' => $operatingSystem->deleted_at?->format('d/m/Y H:i:s'),
            ];

            $this->showViewModal = true;
        }
    }

    #[On('editOperatingSystem')]
    public function editOperatingSystem($rowId): void
    {
        $operatingSystem = OperatingSystem::find($rowId);

        if ($operatingSystem) {
            $this->editForm->setOperatingSystem($operatingSystem);
            $this->showEditModal = true;
        }
    }

    #[On('openBulkStatusModal')]
    public function bulkChangeStatus(array $ids): void
    {
        $this->showBulkStatusModal = true;
        $this->bulkSelectedIds = $ids;
    }
}
