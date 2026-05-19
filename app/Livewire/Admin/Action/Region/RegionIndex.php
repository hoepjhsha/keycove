<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Region;

use App\Contracts\Repositories\RegionRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Livewire\Admin\Form\Region\RegionBulkChangeStatusForm;
use App\Livewire\Admin\Form\Region\RegionCreateForm;
use App\Livewire\Admin\Form\Region\RegionEditForm;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manage Regions')]
class RegionIndex extends Component
{
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showViewModal = false;

    public bool $showBulkStatusModal = false;

    public ?array $viewData = null;

    public array $bulkSelectedIds = [];

    public RegionCreateForm $createForm;

    public RegionEditForm $editForm;

    public RegionBulkChangeStatusForm $bulkChangeStatusForm;

    protected RegionRepositoryInterface $regions;

    public function boot(RegionRepositoryInterface $regions): void
    {
        $this->regions = $regions;
    }

    #[Computed]
    public function parentRegions()
    {
        return $this->regions->getParentOptions();
    }

    public function createRegion(): void
    {
        $result = $this->createForm->store();
        if ($result) {
            $this->reset('createForm');
            sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.created', ['Name' => __('admin.nav.regions')]));
            $this->dispatch('pg:eventRefresh-regionTable');
        } else {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.create_failed', ['name' => __('admin.nav.regions')]));
        }

        $this->showCreateModal = false;
    }

    public function updateRegion(): void
    {
        $result = $this->editForm->update();
        if ($result) {
            sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.updated', ['Name' => __('admin.nav.regions')]));
            $this->dispatch('pg:eventRefresh-regionTable');
        } else {
            sweetalert()->error(__('admin.messages.update_failed', ['name' => __('admin.nav.regions')]));
        }
        $this->showEditModal = false;
    }

    public function bulkChangeStatusRegion(): void
    {
        if ($this->regions->hasDeletedStatus($this->bulkSelectedIds)) {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.cannot_change_deleted_status'));
            $this->showBulkStatusModal = false;

            return;
        }

        $result = $this->bulkChangeStatusForm->setStatus($this->bulkSelectedIds);

        if ($result) {
            sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.status_updated_selected'));

            $this->bulkChangeStatusForm->reset();
            $this->bulkSelectedIds = [];

            $this->dispatch('pg:eventRefresh-regionTable');
        } else {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.failed_update_status'));
        }

        $this->showBulkStatusModal = false;
    }

    public function render()
    {
        return view('pages.admin.region.index')->layout('components.layouts.dashboard');
    }

    #[On('openCreateModal')]
    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    #[On('viewRegion')]
    public function viewRegion($rowId): void
    {
        try {
            $region = $this->regions->findForAdminOrFail((int) $rowId);
        } catch (ModelNotFoundException) {
            return;
        }

        $colorClass = match ($region->status) {
            GeneralStatus::Active   => 'bg-green-500/10 text-green-500',
            GeneralStatus::Inactive => 'bg-gray-500/10 text-gray-500',
            GeneralStatus::Hidden   => 'bg-yellow-500/10 text-yellow-500',
            GeneralStatus::Deleted  => 'bg-red-500/10 text-red-500',
            default                 => 'bg-primary-500/10 text-primary-500',
        };

        $statusLabel = '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$region->status->label().'</span>';

        $this->viewData = [
            'id'           => $region->id,
            'name'         => $region->name,
            'slug'         => $region->slug,
            'flag_code'    => $region->flag_code,
            'parent_name'  => $region->parent->name ?? __('admin.common.none'),
            'status_label' => $statusLabel,
            'created_at'   => $region->created_at->format('d/m/Y H:i:s'),
            'updated_at'   => $region->updated_at->format('d/m/Y H:i:s'),
            'deleted_at'   => $region->deleted_at?->format('d/m/Y H:i:s'),
        ];

        $this->showViewModal = true;
    }

    #[On('editRegion')]
    public function editRegion($rowId): void
    {
        try {
            $region = $this->regions->findForAdminOrFail((int) $rowId);
        } catch (ModelNotFoundException) {
            return;
        }

        $this->editForm->setRegion($region);
        $this->showEditModal = true;
    }

    #[On('openBulkStatusModal')]
    public function bulkChangeStatus(array $ids): void
    {
        $this->showBulkStatusModal = true;
        $this->bulkSelectedIds = $ids;
    }
}
