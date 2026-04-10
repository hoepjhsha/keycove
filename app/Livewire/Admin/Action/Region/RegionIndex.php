<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Region;

use App\Enums\GeneralStatus;
use App\Livewire\Admin\Form\Region\RegionBulkChangeStatusForm;
use App\Livewire\Admin\Form\Region\RegionCreateForm;
use App\Livewire\Admin\Form\Region\RegionEditForm;
use App\Models\Region;
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

    #[Computed]
    public function parentRegions()
    {
        return Region::select('id', 'name')->get();
    }

    public function createRegion(): void
    {
        $result = $this->createForm->store();
        if ($result) {
            $this->reset('createForm');
            sweetalert()->title('Success!')->showConfirmButton(false)->success('Region created successfully');
            $this->dispatch('pg:eventRefresh-regionTable');
        } else {
            sweetalert()->title('Error!')->showConfirmButton(false)->error('Failed to create region');
        }

        $this->showCreateModal = false;
    }

    public function updateRegion(): void
    {
        $result = $this->editForm->update();
        if ($result) {
            sweetalert()->title('Success!')->showConfirmButton(false)->success('Region updated successfully');
            $this->dispatch('pg:eventRefresh-regionTable');
        } else {
            sweetalert()->error('Failed to update region');
        }
        $this->showEditModal = false;
    }

    public function bulkChangeStatusRegion(): void
    {
        if (Region::withTrashed()
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

            $this->dispatch('pg:eventRefresh-regionTable');
        } else {
            sweetalert()->title('Error!')->showConfirmButton(false)->error('Failed to update status.');
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
        $region = Region::with('parent')->find($rowId);

        if ($region) {
            $colorClass = match ($region->status) {
                GeneralStatus::Active => 'bg-green-500/10 text-green-500',
                GeneralStatus::Inactive => 'bg-gray-500/10 text-gray-500',
                GeneralStatus::Hidden => 'bg-yellow-500/10 text-yellow-500',
                GeneralStatus::Deleted => 'bg-red-500/10 text-red-500',
                default => 'bg-primary-500/10 text-primary-500',
            };

            $statusLabel = '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$region->status->label().'</span>';

            $this->viewData = [
                'id' => $region->id,
                'name' => $region->name,
                'slug' => $region->slug,
                'flag_code' => $region->flag_code,
                'parent_name' => $region->parent->name ?? '--None--',
                'status_label' => $statusLabel,
                'created_at' => $region->created_at->format('d/m/Y H:i:s'),
                'updated_at' => $region->updated_at->format('d/m/Y H:i:s'),
                'deleted_at' => $region->deleted_at?->format('d/m/Y H:i:s'),
            ];

            $this->showViewModal = true;
        }
    }

    #[On('editRegion')]
    public function editRegion($rowId): void
    {
        $region = Region::find($rowId);

        if ($region) {
            $this->editForm->setRegion($region);
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
