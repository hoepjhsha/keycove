<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Product;

use App\Enums\GeneralStatus;
use App\Livewire\Admin\Form\Product\ProductBulkChangeStatusForm;
use App\Livewire\Admin\Form\Product\ProductCreateForm;
use App\Livewire\Admin\Form\Product\ProductEditForm;
use App\Models\Category;
use App\Models\Product;
use App\Utilities\StorageUtility;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Manage Products')]
class ProductIndex extends Component
{
    use WithFileUploads;

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showViewModal = false;

    public bool $showBulkStatusModal = false;

    public ?array $viewData = null;

    public array $bulkSelectedIds = [];

    public ProductCreateForm $createForm;

    public ProductEditForm $editForm;

    public ProductBulkChangeStatusForm $bulkChangeStatusForm;

    public function addSystemRequirement(): void
    {
        $this->createForm->addSystemRequirement();
    }

    public function removeSystemRequirement(int $index): void
    {
        $this->createForm->removeSystemRequirement($index);
    }

    public function createProduct(): void
    {
        $result = $this->createForm->store();
        if ($result) {
            $this->createForm->reset();
            sweetalert()->title('Success!')->showConfirmButton(false)->success('Product created successfully');
            $this->dispatch('pg:eventRefresh-productTable');
        } else {
            sweetalert()->title('Error!')->showConfirmButton(false)->error('Failed to create product');
        }

        $this->showCreateModal = false;
    }

    public function updateProduct(): void
    {
        $result = $this->editForm->update();
        if ($result) {
            sweetalert()->title('Success!')->showConfirmButton(false)->success('Product updated successfully');
            $this->dispatch('pg:eventRefresh-productTable');
        } else {
            sweetalert()->error('Failed to update product');
        }
        $this->showEditModal = false;
    }

    public function bulkChangeStatusProduct(): void
    {
        if (Product::withTrashed()
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

            $this->dispatch('pg:eventRefresh-productTable');
        } else {
            sweetalert()->title('Error!')->showConfirmButton(false)->error('Failed to update status.');
        }

        $this->showBulkStatusModal = false;
    }

    public function render()
    {
        $categories = Category::whereNull('parent_id')
            ->where('status', GeneralStatus::Active)
            ->with(['children' => function ($query) {
                $query->where('status', GeneralStatus::Active);
            }])
            ->get();

        $categoriesGrouped = $categories->mapWithKeys(function ($parent) {
            return [
                $parent->id => [
                    'name'     => $parent->name,
                    'children' => $parent->children,
                ],
            ];
        })->toArray();

        return view('pages.admin.product.index', [
            'categoriesGrouped' => $categoriesGrouped,
        ])->layout('components.layouts.dashboard');
    }

    #[On('openCreateModal')]
    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    #[On('viewProduct')]
    public function viewProduct($rowId): void
    {
        $product = Product::with('categories')->find($rowId);

        if ($product) {
            $colorClass = match ($product->status) {
                GeneralStatus::Active   => 'bg-green-500/10 text-green-500',
                GeneralStatus::Inactive => 'bg-gray-500/10 text-gray-500',
                GeneralStatus::Hidden   => 'bg-yellow-500/10 text-yellow-500',
                GeneralStatus::Deleted  => 'bg-red-500/10 text-red-500',
                default                 => 'bg-primary-500/10 text-primary-500',
            };

            $statusLabel = '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$product->status->label().'</span>';

            $categories = $product->categories->isNotEmpty() ? $product->categories : collect();

            $imageUrl = null;
            if ($product->image_thumbnail_path) {
                $imageUrl = StorageUtility::getUrl($product->image_thumbnail_path);
            }

            $this->viewData = [
                'id'                 => $product->id,
                'name'               => $product->name,
                'slug'               => $product->slug,
                'image_url'          => $imageUrl,
                'publisher'          => $product->publisher ?? '--N/A--',
                'developer'          => $product->developer ?? '--N/A--',
                'release_date'       => $product->release_date ? $product->release_date->format('d/m/Y') : '--N/A--',
                'description'        => $product->description ?? '--N/A--',
                'system_requirement' => $product->system_requirement ?? [],
                'categories'         => $categories,
                'status_label'       => $statusLabel,
                'created_at'         => $product->created_at->format('d/m/Y H:i:s'),
                'updated_at'         => $product->updated_at->format('d/m/Y H:i:s'),
                'deleted_at'         => $product->deleted_at?->format('d/m/Y H:i:s'),
            ];

            $this->showViewModal = true;
        }
    }

    #[On('editProduct')]
    public function editProduct($rowId): void
    {
        $product = Product::find($rowId);

        if ($product) {
            $this->editForm->setProduct($product);
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
