<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Category;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Livewire\Admin\Form\Category\CategoryBulkChangeStatusForm;
use App\Livewire\Admin\Form\Category\CategoryCreateForm;
use App\Livewire\Admin\Form\Category\CategoryEditForm;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manage Categories')]
class CategoryIndex extends Component
{
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showViewModal = false;

    public bool $showBulkStatusModal = false;

    public ?array $viewData = null;

    public array $bulkSelectedIds = [];

    public CategoryCreateForm $createForm;

    public CategoryEditForm $editForm;

    public CategoryBulkChangeStatusForm $bulkChangeStatusForm;

    protected CategoryRepositoryInterface $categories;

    public function boot(CategoryRepositoryInterface $categories): void
    {
        $this->categories = $categories;
    }

    #[Computed]
    public function parentCategories()
    {
        return $this->categories->getParentOptions();
    }

    public function createCategory(): void
    {
        $result = $this->createForm->store();
        if ($result) {
            $this->reset('createForm');
            sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.created', ['Name' => __('admin.nav.categories')]));
            $this->dispatch('pg:eventRefresh-categoryTable');
        } else {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.create_failed', ['name' => __('admin.nav.categories')]));
        }

        $this->showCreateModal = false;
    }

    public function updateCategory(): void
    {
        $result = $this->editForm->update();
        if ($result) {
            sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.updated', ['Name' => __('admin.nav.categories')]));
            $this->dispatch('pg:eventRefresh-categoryTable');
        } else {
            sweetalert()->error(__('admin.messages.update_failed', ['name' => __('admin.nav.categories')]));
        }
        $this->showEditModal = false;
    }

    public function bulkChangeStatusCategory(): void
    {
        if ($this->categories->hasDeletedStatus($this->bulkSelectedIds)) {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.cannot_change_deleted_status'));
            $this->showBulkStatusModal = false;

            return;
        }

        $result = $this->bulkChangeStatusForm->setStatus($this->bulkSelectedIds);

        if ($result) {
            sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.status_updated_selected'));

            $this->bulkChangeStatusForm->reset();
            $this->bulkSelectedIds = [];

            $this->dispatch('pg:eventRefresh-categoryTable');
        } else {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.failed_update_status'));
        }

        $this->showBulkStatusModal = false;
    }

    public function render()
    {
        return view('pages.admin.category.index')->layout('components.layouts.dashboard');
    }

    #[On('openCreateModal')]
    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    #[On('viewCategory')]
    public function viewCategory($rowId): void
    {
        try {
            $category = $this->categories->findForAdminOrFail((int) $rowId);
        } catch (ModelNotFoundException) {
            return;
        }

        $colorClass = match ($category->status) {
            GeneralStatus::Active   => 'bg-green-500/10 text-green-500',
            GeneralStatus::Inactive => 'bg-gray-500/10 text-gray-500',
            GeneralStatus::Hidden   => 'bg-yellow-500/10 text-yellow-500',
            GeneralStatus::Deleted  => 'bg-red-500/10 text-red-500',
            default                 => 'bg-primary-500/10 text-primary-500',
        };

        $statusLabel = '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$category->status->label().'</span>';

        $this->viewData = [
            'id'           => $category->id,
            'name'         => $category->name,
            'slug'         => $category->slug,
            'parent_name'  => $category->parent->name ?? __('admin.common.none'),
            'status_label' => $statusLabel,
            'created_at'   => $category->created_at->format('d/m/Y H:i:s'),
            'updated_at'   => $category->updated_at->format('d/m/Y H:i:s'),
            'deleted_at'   => $category->deleted_at?->format('d/m/Y H:i:s'),
        ];

        $this->showViewModal = true;
    }

    #[On('editCategory')]
    public function editCategory($rowId): void
    {
        try {
            $category = $this->categories->findForAdminOrFail((int) $rowId);
        } catch (ModelNotFoundException) {
            return;
        }

        $this->editForm->setCategory($category);
        $this->showEditModal = true;
    }

    #[On('openBulkStatusModal')]
    public function bulkChangeStatus(array $ids): void
    {
        $this->showBulkStatusModal = true;
        $this->bulkSelectedIds = $ids;
    }
}
