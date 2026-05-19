<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Product;

use App\Contracts\Repositories\OperatingSystemRepositoryInterface;
use App\Contracts\Repositories\PlatformRepositoryInterface;
use App\Contracts\Repositories\ProductKeyRepositoryInterface;
use App\Contracts\Repositories\ProductListingRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Contracts\Repositories\RegionRepositoryInterface;
use App\Contracts\Repositories\SellerRepositoryInterface;
use App\Livewire\Admin\Form\Product\ProductKeyForm;
use App\Livewire\Admin\Form\Product\ProductListingForm;
use App\Livewire\Admin\Form\Product\ProductVariantForm;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
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

    protected ProductRepositoryInterface $productRepository;

    protected ProductVariantRepositoryInterface $productVariantRepository;

    protected ProductListingRepositoryInterface $productListingRepository;

    protected ProductKeyRepositoryInterface $productKeyRepository;

    protected RegionRepositoryInterface $regionRepository;

    protected PlatformRepositoryInterface $platformRepository;

    protected OperatingSystemRepositoryInterface $operatingSystemRepository;

    protected SellerRepositoryInterface $sellerRepository;

    public function boot(
        ProductRepositoryInterface $productRepository,
        ProductVariantRepositoryInterface $productVariantRepository,
        ProductListingRepositoryInterface $productListingRepository,
        ProductKeyRepositoryInterface $productKeyRepository,
        RegionRepositoryInterface $regionRepository,
        PlatformRepositoryInterface $platformRepository,
        OperatingSystemRepositoryInterface $operatingSystemRepository,
        SellerRepositoryInterface $sellerRepository,
    ): void {
        $this->productRepository = $productRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->productListingRepository = $productListingRepository;
        $this->productKeyRepository = $productKeyRepository;
        $this->regionRepository = $regionRepository;
        $this->platformRepository = $platformRepository;
        $this->operatingSystemRepository = $operatingSystemRepository;
        $this->sellerRepository = $sellerRepository;
    }

    #[Computed]
    public function productVariants(): Collection
    {
        if (! $this->product) {
            return new Collection;
        }

        return $this->productVariantRepository->getForAdminProductDetail($this->product->id, $this->showTrashedVariants);
    }

    #[Computed]
    public function productListings(): Collection
    {
        if (! $this->product) {
            return new Collection;
        }

        return $this->productListingRepository->getForAdminProductDetail($this->product->id, $this->showTrashedListings);
    }

    #[Computed]
    public function regions(): Collection
    {
        return $this->regionRepository->getActiveOrdered();
    }

    #[Computed]
    public function platforms(): Collection
    {
        return $this->platformRepository->getActiveOrdered();
    }

    #[Computed]
    public function operatingSystems(): Collection
    {
        return $this->operatingSystemRepository->getActiveOrdered();
    }

    #[Computed]
    public function sellers(): Collection
    {
        return $this->sellerRepository->getApprovedWithUser();
    }

    #[Computed]
    public function viewingListingKeys(): Collection
    {
        if (! $this->viewingKeysListingId) {
            return new Collection;
        }

        return $this->productKeyRepository->getForAdminListing($this->viewingKeysListingId);
    }

    public function mount(int $id): void
    {
        $this->product = $this->productRepository->findForAdminDetailOrFail($id);
    }

    #[On('openVariantModal')]
    public function openVariantModal(?int $variantId = null): void
    {
        if ($variantId) {
            $variant = $this->productVariantRepository->findForAdminDetailOrFail($variantId, true);
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
        try {
            if ($this->editingVariantId) {
                $result = $this->variantForm->update();
                $message = __('admin.messages.updated', ['Name' => __('admin.common.variant')]);
            } else {
                $result = $this->variantForm->store();
                $message = __('admin.messages.created', ['Name' => __('admin.common.variant')]);
            }

            if ($result) {
                $this->variantForm->resetForm();
                $this->showVariantModal = false;
                $this->dispatch('swal:success', ['message' => $message]);
                $this->dispatch('pg:eventRefresh-productVariantsTable');
                $this->refreshProduct();
            }
        } catch (ValidationException $exception) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($exception)]);
        }
    }

    #[On('openListingModal')]
    public function openListingModal(int $variantId, ?int $listingId = null): void
    {
        $this->selectedVariantIdForListing = $variantId;

        if ($listingId) {
            $listing = $this->productListingRepository->findForAdminDetailOrFail($listingId, true);
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
        try {
            $this->listingForm->variant_id = $this->selectedVariantIdForListing;

            if ($this->editingListingId) {
                $result = $this->listingForm->update();
                $message = __('admin.messages.updated', ['Name' => __('admin.common.listing')]);
            } else {
                $result = $this->listingForm->store();
                $message = __('admin.messages.created', ['Name' => __('admin.common.listing')]);
            }

            if ($result) {
                $this->listingForm->resetForm();
                $this->showListingModal = false;
                $this->dispatch('swal:success', ['message' => $message]);
                $this->dispatch('pg:eventRefresh-productListingsTable');
                $this->refreshProduct();
            }
        } catch (ValidationException $exception) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($exception)]);
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
        try {
            $result = $this->keyForm->store();

            if ($result) {
                $this->keyForm->resetForm();
                $this->dispatch('swal:success', ['message' => __('admin.messages.created', ['Name' => __('admin.common.product_key')])]);
                $this->dispatch('pg:eventRefresh-productListingsTable');
                $this->dispatch('pg:eventRefresh-productKeysTable');
            }
        } catch (ValidationException $exception) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($exception)]);
        }
    }

    public function deleteKey(int $keyId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_delete', ['name' => __('admin.common.product_key')]),
            'text'   => __('admin.swal.delete_text', ['name' => __('admin.common.product_key')]),
            'method' => 'performDeleteKey',
            'id'     => $keyId,
        ]);
    }

    #[On('performDeleteKey')]
    public function performDeleteKey(int $id): void
    {
        try {
            $this->keyForm->deleteKey($id);
            $this->dispatch('swal:success', ['message' => __('admin.messages.deleted_sentence', ['Name' => __('admin.common.product_key')])]);
            $this->dispatch('pg:eventRefresh-productListingsTable');
        } catch (ValidationException $exception) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($exception)]);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function deleteVariant(int $rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_delete', ['name' => __('admin.common.variant')]),
            'text'   => __('admin.swal.delete_text', ['name' => __('admin.common.variant')]),
            'method' => 'performDeleteVariant',
            'id'     => $rowId,
        ]);
    }

    #[On('performDeleteVariant')]
    public function performDeleteVariant(int $id): void
    {
        try {
            $this->variantForm->deleteVariant($id);
            $this->dispatch('swal:success', ['message' => __('admin.messages.deleted_sentence', ['Name' => __('admin.common.variant')])]);
            $this->dispatch('pg:eventRefresh-productVariantsTable');
            $this->refreshProduct();
        } catch (ValidationException $exception) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($exception)]);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function restoreVariant(int $id): void
    {
        try {
            $this->variantForm->restoreVariant($id);
            $this->dispatch('swal:success', ['message' => __('admin.messages.restored_sentence', ['Name' => __('admin.common.variant')])]);
            $this->dispatch('pg:eventRefresh-productVariantsTable');
            $this->refreshProduct();
        } catch (ValidationException $exception) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($exception)]);
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
            $this->dispatch('swal:success', ['message' => __('admin.messages.status_updated_selected')]);
            $this->dispatch('pg:eventRefresh-productVariantsTable');
            $this->refreshProduct();
        } catch (ValidationException $exception) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($exception)]);
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
            $this->dispatch('swal:success', ['message' => __('admin.messages.status_updated_selected')]);
            $this->dispatch('pg:eventRefresh-productListingsTable');
            $this->refreshProduct();
        } catch (ValidationException $exception) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($exception)]);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    protected function refreshProduct(): void
    {
        $this->product = $this->productRepository->findForAdminDetailOrFail($this->product->id);
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
        return $this->productListingRepository->getForAdminVariantDetail($variantId, $this->showTrashedListings);
    }

    public function deleteListing(int $listingId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_delete', ['name' => __('admin.common.listing')]),
            'text'   => __('admin.swal.delete_text', ['name' => __('admin.common.listing')]),
            'method' => 'performDeleteListing',
            'id'     => $listingId,
        ]);
    }

    #[On('performDeleteListing')]
    public function performDeleteListing(int $id): void
    {
        try {
            $this->listingForm->deleteListing($id);
            $this->dispatch('swal:success', ['message' => __('admin.messages.deleted_sentence', ['Name' => __('admin.common.listing')])]);
            $this->dispatch('pg:eventRefresh-productListingsTable');
            $this->refreshProduct();
        } catch (ValidationException $exception) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($exception)]);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function restoreListing(int $id): void
    {
        try {
            $this->listingForm->restoreListing($id);
            $this->dispatch('swal:success', ['message' => __('admin.messages.restored_sentence', ['Name' => __('admin.common.listing')])]);
            $this->dispatch('pg:eventRefresh-productListingsTable');
            $this->refreshProduct();
        } catch (ValidationException $exception) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($exception)]);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    protected function firstValidationMessage(ValidationException $exception): string
    {
        return collect($exception->errors())
            ->flatten()
            ->filter(fn ($message): bool => is_string($message) && $message !== '')
            ->first() ?? $exception->getMessage();
    }

    public function render()
    {
        return view('pages.admin.product.detail')
            ->layout('components.layouts.dashboard');
    }
}
