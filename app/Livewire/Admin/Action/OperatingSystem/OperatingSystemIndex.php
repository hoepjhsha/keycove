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
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Title('Manage OperatingSystems')]
class OperatingSystemIndex extends Component
{
    use WithFileUploads;

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
            $this->createForm->reset();
            sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.created', ['Name' => __('admin.nav.operating_systems')]));
            $this->dispatch('pg:eventRefresh-operatingSystemTable');
        } else {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.create_failed', ['name' => __('admin.nav.operating_systems')]));
        }

        $this->showCreateModal = false;
    }

    public function updateOperatingSystem(): void
    {
        $result = $this->editForm->update();
        if ($result) {
            sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.updated', ['Name' => __('admin.nav.operating_systems')]));
            $this->dispatch('pg:eventRefresh-operatingSystemTable');
        } else {
            sweetalert()->error(__('admin.messages.update_failed', ['name' => __('admin.nav.operating_systems')]));
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
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.cannot_change_deleted_status'));
            $this->showBulkStatusModal = false;

            return;
        }

        $result = $this->bulkChangeStatusForm->setStatus($this->bulkSelectedIds);

        if ($result) {
            sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.status_updated_selected'));

            $this->bulkChangeStatusForm->reset();
            $this->bulkSelectedIds = [];

            $this->dispatch('pg:eventRefresh-operatingSystemTable');
        } else {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.failed_update_status'));
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
                GeneralStatus::Active   => 'bg-green-500/10 text-green-500',
                GeneralStatus::Inactive => 'bg-gray-500/10 text-gray-500',
                GeneralStatus::Hidden   => 'bg-yellow-500/10 text-yellow-500',
                GeneralStatus::Deleted  => 'bg-red-500/10 text-red-500',
                default                 => 'bg-primary-500/10 text-primary-500',
            };

            $statusLabel = '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$operatingSystem->status->label().'</span>';

            $this->viewData = [
                'id'           => $operatingSystem->id,
                'name'         => $operatingSystem->name,
                'slug'         => $operatingSystem->slug,
                'icon_path'    => $operatingSystem->icon_path,
                'status_label' => $statusLabel,
                'created_at'   => $operatingSystem->created_at->format('d/m/Y H:i:s'),
                'updated_at'   => $operatingSystem->updated_at->format('d/m/Y H:i:s'),
                'deleted_at'   => $operatingSystem->deleted_at?->format('d/m/Y H:i:s'),
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
