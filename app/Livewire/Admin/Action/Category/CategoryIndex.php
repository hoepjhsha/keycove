<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Category;

use App\Enums\GeneralStatus;
use App\Livewire\Admin\Form\Category\CategoryBulkChangeStatusForm;
use App\Livewire\Admin\Form\Category\CategoryCreateForm;
use App\Livewire\Admin\Form\Category\CategoryEditForm;
use App\Models\Category;
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

    #[Computed]
    public function parentCategories()
    {
        return Category::select('id', 'name')->get();
    }

    public function createCategory(): void
    {
        $result = $this->createForm->store();
        if ($result) {
            $this->reset('createForm');
            sweetalert()->title('Success!')->showConfirmButton(false)->success('Category created successfully');
            $this->dispatch('pg:eventRefresh-categoryTable');
        } else {
            sweetalert()->title('Error!')->showConfirmButton(false)->error('Failed to create category');
        }

        $this->showCreateModal = false;
    }

    public function updateCategory(): void
    {
        $result = $this->editForm->update();
        if ($result) {
            sweetalert()->title('Success!')->showConfirmButton(false)->success('Category updated successfully');
            $this->dispatch('pg:eventRefresh-categoryTable');
        } else {
            sweetalert()->error('Failed to update category');
        }
        $this->showEditModal = false;
    }

    public function bulkChangeStatusCategory(): void
    {
        if (Category::withTrashed()
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

            $this->dispatch('pg:eventRefresh-categoryTable');
        } else {
            sweetalert()->title('Error!')->showConfirmButton(false)->error('Failed to update status.');
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
        $category = Category::with('parent')->find($rowId);

        if ($category) {
            $colorClass = match ($category->status) {
                GeneralStatus::Active => 'bg-green-500/10 text-green-500',
                GeneralStatus::Inactive => 'bg-gray-500/10 text-gray-500',
                GeneralStatus::Hidden => 'bg-yellow-500/10 text-yellow-500',
                GeneralStatus::Deleted => 'bg-red-500/10 text-red-500',
                default => 'bg-primary-500/10 text-primary-500',
            };

            $statusLabel = '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$category->status->label().'</span>';

            $this->viewData = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_name' => $category->parent->name ?? '--None--',
                'status_label' => $statusLabel,
                'created_at' => $category->created_at->format('d/m/Y H:i:s'),
                'updated_at' => $category->updated_at->format('d/m/Y H:i:s'),
                'deleted_at' => $category->deleted_at?->format('d/m/Y H:i:s'),
            ];

            $this->showViewModal = true;
        }
    }

    #[On('editCategory')]
    public function editCategory($rowId): void
    {
        $category = Category::find($rowId);

        if ($category) {
            $this->editForm->setCategory($category);
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
