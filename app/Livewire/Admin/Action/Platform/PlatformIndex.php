<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Platform;

use App\Enums\GeneralStatus;
use App\Livewire\Admin\Form\Platform\PlatformBulkChangeStatusForm;
use App\Livewire\Admin\Form\Platform\PlatformCreateForm;
use App\Livewire\Admin\Form\Platform\PlatformEditForm;
use App\Models\Platform;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Title('Manage Platforms')]
class PlatformIndex extends Component
{
    use WithFileUploads;

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showViewModal = false;

    public bool $showBulkStatusModal = false;

    public ?array $viewData = null;

    public array $bulkSelectedIds = [];

    public PlatformCreateForm $createForm;

    public PlatformEditForm $editForm;

    public PlatformBulkChangeStatusForm $bulkChangeStatusForm;

    public function createPlatform(): void
    {
        $result = $this->createForm->store();

        if ($result) {
            $this->createForm->reset();

            sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.created', ['Name' => __('admin.nav.platforms')]));
            $this->dispatch('pg:eventRefresh-platformTable');
        } else {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.create_failed', ['name' => __('admin.nav.platforms')]));
        }

        $this->showCreateModal = false;
    }

    public function updatePlatform(): void
    {
        $result = $this->editForm->update();
        if ($result) {
            $this->editForm->reset();
            sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.updated', ['Name' => __('admin.nav.platforms')]));
            $this->dispatch('pg:eventRefresh-platformTable');
        } else {
            sweetalert()->error(__('admin.messages.update_failed', ['name' => __('admin.nav.platforms')]));
        }
        $this->showEditModal = false;
    }

    public function bulkChangeStatusPlatform(): void
    {
        if (Platform::withTrashed()
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

            $this->dispatch('pg:eventRefresh-platformTable');
        } else {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.failed_update_status'));
        }

        $this->showBulkStatusModal = false;
    }

    public function render()
    {
        return view('pages.admin.platform.index')->layout('components.layouts.dashboard');
    }

    #[On('openCreateModal')]
    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    #[On('viewPlatform')]
    public function viewPlatform($rowId): void
    {
        $platform = Platform::find($rowId);

        if ($platform) {
            $colorClass = match ($platform->status) {
                GeneralStatus::Active   => 'bg-green-500/10 text-green-500',
                GeneralStatus::Inactive => 'bg-gray-500/10 text-gray-500',
                GeneralStatus::Hidden   => 'bg-yellow-500/10 text-yellow-500',
                GeneralStatus::Deleted  => 'bg-red-500/10 text-red-500',
                default                 => 'bg-primary-500/10 text-primary-500',
            };

            $statusLabel = '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$platform->status->label().'</span>';

            $this->viewData = [
                'id'           => $platform->id,
                'name'         => $platform->name,
                'slug'         => $platform->slug,
                'icon_path'    => $platform->icon_path,
                'base_url'     => $platform->base_url,
                'status_label' => $statusLabel,
                'created_at'   => $platform->created_at->format('d/m/Y H:i:s'),
                'updated_at'   => $platform->updated_at->format('d/m/Y H:i:s'),
                'deleted_at'   => $platform->deleted_at?->format('d/m/Y H:i:s'),
            ];

            $this->showViewModal = true;
        }
    }

    #[On('editPlatform')]
    public function editPlatform($rowId): void
    {
        $platform = Platform::find($rowId);

        if ($platform) {
            $this->editForm->setPlatform($platform);
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
