<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Product;

use App\Enums\GeneralStatus;
use App\Enums\KycStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Livewire\Admin\Form\Product\ProductKeyForm;
use App\Livewire\Admin\Form\Product\ProductListingForm;
use App\Livewire\Admin\Form\Product\ProductVariantForm;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Product Details')]
class ProductDetail extends Component
{
    public Product $product;

    public bool $showVariantModal = false;

    public bool $showListingModal = false;

    public bool $showKeysModal = false;

    public bool $showVariantBulkStatusModal = false;

    public bool $showListingBulkStatusModal = false;

    public bool $showTrashedVariants = false;

    public bool $showTrashedListings = false;

    public ?int $editingVariantId = null;

    public ?int $editingListingId = null;

    public ?int $viewingKeysListingId = null;

    public ?int $selectedVariantIdForListing = null;

    public array $variantBulkSelectedIds = [];

    public array $listingBulkSelectedIds = [];

    public ProductVariantForm $variantForm;

    public ProductListingForm $listingForm;

    public ProductKeyForm $keyForm;

    public int $variantBulkStatusForm_status = 0;

    public int $listingBulkStatusForm_status = 0;

    public array $expandedVariantRows = [];

    #[Computed]
    public function productVariants()
    {
        if (! $this->product) {
            return collect();
        }

        return ProductVariant::where('product_id', $this->product->id)
            ->when(! $this->showTrashedVariants, fn ($q) => $q->withoutTrashed())
            ->when($this->showTrashedVariants, fn ($q) => $q->withTrashed())
            ->with(['region', 'platform', 'operatingSystem'])
            ->withCount(['listings' => function ($q) {
                $q->where('status', '!=', ProductListingStatus::Deleted);
            }])
            ->get();
    }

    #[Computed]
    public function productListings()
    {
        if (! $this->product) {
            return collect();
        }

        return ProductListing::whereIn('variant_id', $this->product->variants()->pluck('id'))
            ->when(! $this->showTrashedListings, fn ($q) => $q->withoutTrashed())
            ->when($this->showTrashedListings, fn ($q) => $q->withTrashed())
            ->with(['seller.user', 'variant'])
            ->withCount(['keys' => function ($query) {
                $query->where('status', ProductKeyStatus::Available->value);
            }])
            ->get();
    }

    #[Computed]
    public function regions()
    {
        return Region::where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function platforms()
    {
        return Platform::where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function operatingSystems()
    {
        return OperatingSystem::where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function sellers()
    {
        return Seller::where('kyc_status', KycStatus::Approved)
            ->with('user')
            ->orderBy('shop_name')
            ->get();
    }

    #[Computed]
    public function viewingListingKeys()
    {
        if (! $this->viewingKeysListingId) {
            return collect();
        }

        return ProductKey::where('listing_id', $this->viewingKeysListingId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function mount(int $id): void
    {
        $this->product = Product::with(['categories', 'submittedBySeller.user'])->findOrFail($id);
    }

    #[On('openVariantModal')]
    public function openVariantModal(?int $variantId = null): void
    {
        if ($variantId) {
            $variant = ProductVariant::findOrFail($variantId);
            $this->variantForm->setVariant($variant);
            $this->editingVariantId = $variantId;
        } else {
            $this->variantForm->resetForm();
            $this->variantForm->product_id = $this->product->id;
            $this->editingVariantId = null;
        }

        $this->showVariantModal = true;
    }

    #[On('openVariantBulkStatusModal')]
    public function openVariantBulkStatusModal(array $ids): void
    {
        $this->variantBulkSelectedIds = $ids;
        $this->showVariantBulkStatusModal = true;
    }

    public function saveVariant(): void
    {
        if ($this->editingVariantId) {
            $result = $this->variantForm->update();
            $message = 'Variant updated successfully';
        } else {
            $result = $this->variantForm->store();
            $message = 'Variant created successfully';
        }

        if ($result) {
            $this->variantForm->resetForm();
            $this->showVariantModal = false;
            $this->dispatch('swal:success', ['message' => $message]);
            $this->dispatch('pg:eventRefresh-productVariantsTable');
            $this->refreshProduct();
        }
    }

    #[On('openListingModal')]
    public function openListingModal(int $variantId, ?int $listingId = null): void
    {
        $this->selectedVariantIdForListing = $variantId;

        if ($listingId) {
            $listing = ProductListing::findOrFail($listingId);
            $this->listingForm->setListing($listing);
            $this->editingListingId = $listingId;
        } else {
            $this->listingForm->resetForm();
            $this->listingForm->variant_id = $variantId;
            $this->editingListingId = null;
        }

        $this->showListingModal = true;
    }

    #[On('openListingBulkStatusModal')]
    public function openListingBulkStatusModal(array $ids): void
    {
        $this->listingBulkSelectedIds = $ids;
        $this->showListingBulkStatusModal = true;
    }

    public function saveListing(): void
    {
        $this->listingForm->variant_id = $this->selectedVariantIdForListing;

        if ($this->editingListingId) {
            $result = $this->listingForm->update();
            $message = 'Listing updated successfully';
        } else {
            $result = $this->listingForm->store();
            $message = 'Listing created successfully';
        }

        if ($result) {
            $this->listingForm->resetForm();
            $this->showListingModal = false;
            $this->dispatch('swal:success', ['message' => $message]);
            $this->dispatch('pg:eventRefresh-productListingsTable');
            $this->refreshProduct();
        }
    }

    #[On('openKeysModal')]
    public function openKeysModal(int $listingId): void
    {
        $this->viewingKeysListingId = $listingId;
        $this->keyForm->resetForm();
        $this->keyForm->listing_id = $listingId;
        $this->showKeysModal = true;
    }

    public function saveKey(): void
    {
        $result = $this->keyForm->store();

        if ($result) {
            $this->keyForm->resetForm();
            $this->dispatch('swal:success', ['message' => 'Key created successfully']);
            $this->dispatch('pg:eventRefresh-productListingsTable');
            $this->dispatch('pg:eventRefresh-productKeysTable');
        }
    }

    public function deleteKey(int $keyId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Delete Key?',
            'text'   => 'Are you sure you want to delete this key?',
            'method' => 'performDeleteKey',
            'id'     => $keyId,
        ]);
    }

    #[On('performDeleteKey')]
    public function performDeleteKey(int $id): void
    {
        try {
            $this->keyForm->deleteKey($id);
            $this->dispatch('swal:success', ['message' => 'Key deleted successfully']);
            $this->dispatch('pg:eventRefresh-productListingsTable');
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function deleteVariant(int $rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Delete Variant?',
            'text'   => 'Are you sure you want to delete this variant?',
            'method' => 'performDeleteVariant',
            'id'     => $rowId,
        ]);
    }

    #[On('performDeleteVariant')]
    public function performDeleteVariant(int $id): void
    {
        try {
            $this->variantForm->deleteVariant($id);
            $this->dispatch('swal:success', ['message' => 'Variant deleted successfully']);
            $this->dispatch('pg:eventRefresh-productVariantsTable');
            $this->refreshProduct();
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function restoreVariant(int $id): void
    {
        try {
            $this->variantForm->restoreVariant($id);
            $this->dispatch('swal:success', ['message' => 'Variant restored successfully']);
            $this->dispatch('pg:eventRefresh-productVariantsTable');
            $this->refreshProduct();
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function bulkChangeVariantStatus(): void
    {
        try {
            $this->variantForm->bulkChangeStatus($this->variantBulkSelectedIds, $this->variantBulkStatusForm_status);
            $this->showVariantBulkStatusModal = false;
            $this->variantBulkStatusForm_status = 0;
            $this->dispatch('swal:success', ['message' => 'Variants status updated successfully']);
            $this->dispatch('pg:eventRefresh-productVariantsTable');
            $this->refreshProduct();
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function bulkChangeListingStatus(): void
    {
        try {
            $this->listingForm->bulkChangeStatus($this->listingBulkSelectedIds, $this->listingBulkStatusForm_status);
            $this->showListingBulkStatusModal = false;
            $this->listingBulkStatusForm_status = 0;
            $this->dispatch('swal:success', ['message' => 'Listings status updated successfully']);
            $this->dispatch('pg:eventRefresh-productListingsTable');
            $this->refreshProduct();
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    protected function refreshProduct(): void
    {
        $this->product = Product::findOrFail($this->product->id);
    }

    public function toggleVariantRow(int $variantId): void
    {
        if (in_array($variantId, $this->expandedVariantRows)) {
            $this->expandedVariantRows = array_values(array_filter($this->expandedVariantRows, fn ($id) => $id !== $variantId));
        } else {
            $this->expandedVariantRows[] = $variantId;
        }
    }

    public function toggleShowTrashedVariants(): void
    {
        $this->showTrashedVariants = ! $this->showTrashedVariants;
        $this->refreshProduct();
    }

    public function toggleShowTrashedListings(): void
    {
        $this->showTrashedListings = ! $this->showTrashedListings;
    }

    public function isVariantRowExpanded(int $variantId): bool
    {
        return in_array($variantId, $this->expandedVariantRows);
    }

    public function getVariantListings(int $variantId): Collection
    {
        return ProductListing::where('variant_id', $variantId)
            ->when(! $this->showTrashedListings, fn ($q) => $q->withoutTrashed())
            ->when($this->showTrashedListings, fn ($q) => $q->withTrashed())
            ->with(['seller.user'])
            ->withCount(['keys' => function ($query) {
                $query->where('status', ProductKeyStatus::Available->value);
            }])
            ->get();
    }

    public function deleteListing(int $listingId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Delete Listing?',
            'text'   => 'Are you sure you want to delete this listing?',
            'method' => 'performDeleteListing',
            'id'     => $listingId,
        ]);
    }

    #[On('performDeleteListing')]
    public function performDeleteListing(int $id): void
    {
        try {
            $this->listingForm->deleteListing($id);
            $this->dispatch('swal:success', ['message' => 'Listing deleted successfully']);
            $this->dispatch('pg:eventRefresh-productListingsTable');
            $this->refreshProduct();
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function restoreListing(int $id): void
    {
        try {
            $this->listingForm->restoreListing($id);
            $this->dispatch('swal:success', ['message' => 'Listing restored successfully']);
            $this->dispatch('pg:eventRefresh-productListingsTable');
            $this->refreshProduct();
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('pages.admin.product.detail')
            ->layout('components.layouts.dashboard');
    }
}
