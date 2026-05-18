<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Seller;

use App\Contracts\Repositories\ProductKeyRepositoryInterface;
use App\Contracts\Repositories\ProductListingRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Form\Product\ProductCreateForm;
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
use App\Models\User;
use App\Services\ProductKeyService;
use App\Services\ProductListingService;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Listing người bán')]
class SellerListings extends Component
{
    use WithFileUploads;

    protected ProductRepositoryInterface $productRepository;

    protected ProductVariantRepositoryInterface $productVariantRepository;

    protected ProductListingRepositoryInterface $productListingRepository;

    protected ProductKeyRepositoryInterface $productKeyRepository;

    protected ProductService $productService;

    protected ProductVariantService $productVariantService;

    protected ProductListingService $productListingService;

    protected ProductKeyService $productKeyService;

    public bool $showListingModal = false;

    public bool $showKeysModal = false;

    public string $createMode = 'existing_variant';

    public ?int $editingListingId = null;

    public ?int $viewingKeysListingId = null;

    public ?int $selectedProductId = null;

    public ?int $selectedVariantId = null;

    public string $keyCode = '';

    public ProductCreateForm $productForm;

    public ProductVariantForm $variantForm;

    public ProductListingForm $listingForm;

    public function boot(
        ProductRepositoryInterface $productRepository,
        ProductVariantRepositoryInterface $productVariantRepository,
        ProductListingRepositoryInterface $productListingRepository,
        ProductKeyRepositoryInterface $productKeyRepository,
        ProductService $productService,
        ProductVariantService $productVariantService,
        ProductListingService $productListingService,
        ProductKeyService $productKeyService,
    ): void {
        $this->productRepository = $productRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->productListingRepository = $productListingRepository;
        $this->productKeyRepository = $productKeyRepository;
        $this->productService = $productService;
        $this->productVariantService = $productVariantService;
        $this->productListingService = $productListingService;
        $this->productKeyService = $productKeyService;
    }

    #[Computed]
    public function seller(): Seller
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $user->loadMissing('seller');

        abort_unless($user->seller instanceof Seller, 403);
        abort_unless($user->role === UserRole::Seller, 403);

        return $user->seller;
    }

    #[Computed]
    public function metrics(): array
    {
        $seller = $this->seller;

        return [
            'listings'        => $this->productListingRepository->countOwnedBySeller($seller->id),
            'activeListings'  => $this->productListingRepository->countOwnedBySeller($seller->id, ProductListingStatus::Active),
            'pendingListings' => $this->productListingRepository->countOwnedBySeller($seller->id, ProductListingStatus::Draft),
            'products'        => $this->productRepository->countOwnedBySeller($seller->id),
            'availableKeys'   => $this->productKeyRepository->countAvailableForSeller($seller->id),
        ];
    }

    /**
     * @return Collection<int, array{id:int, label:string, source:string, status:string}>
     */
    #[Computed]
    public function productOptions(): Collection
    {
        $seller = $this->seller;

        return $this->productRepository
            ->getAccessibleForSeller($seller->id)
            ->map(function (Product $product) use ($seller): array {
                return [
                    'id'     => $product->id,
                    'label'  => $product->name,
                    'source' => $product->submitted_by_seller_id === $seller->id ? 'Sản phẩm của tôi' : 'Quản trị cửa hàng',
                    'status' => $product->status->label(),
                ];
            });
    }

    /**
     * @return Collection<int, array{id:int, label:string, product:string}>
     */
    #[Computed]
    public function variantOptions(): Collection
    {
        $seller = $this->seller;

        return $this->productVariantRepository
            ->getAccessibleForSeller($seller->id)
            ->map(function (ProductVariant $variant): array {
                return [
                    'id'    => $variant->id,
                    'label' => collect([
                        $variant->product?->name,
                        $variant->edition,
                        $variant->region?->name,
                        $variant->platform?->name,
                        $variant->operatingSystem?->name,
                    ])->filter()->implode(' · '),
                    'product' => $variant->product?->name ?? 'Sản phẩm chưa xác định',
                    'status'  => $variant->status->label(),
                ];
            });
    }

    /**
     * @return Collection<int, Region>
     */
    #[Computed]
    public function regions(): Collection
    {
        return Region::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Platform>
     */
    #[Computed]
    public function platforms(): Collection
    {
        return Platform::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, OperatingSystem>
     */
    #[Computed]
    public function operatingSystems(): Collection
    {
        return OperatingSystem::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function viewingListing(): ?ProductListing
    {
        if ($this->viewingKeysListingId === null) {
            return null;
        }

        return $this->productListingRepository->findOwnedBySellerForView($this->seller->id, $this->viewingKeysListingId);
    }

    /**
     * @return Collection<int, ProductKey>
     */
    #[Computed]
    public function viewingListingKeys(): Collection
    {
        if ($this->viewingKeysListingId === null) {
            return collect();
        }

        return $this->productKeyRepository->getListingKeys($this->viewingKeysListingId);
    }

    public function render(): View
    {
        return view('pages.shop.seller.listings', [
            'seller'  => $this->seller,
            'metrics' => $this->metrics,
        ])->layout('components.layouts.seller', [
            'title'         => 'Listing người bán',
            'user'          => auth()->user(),
            'seller'        => $this->seller,
            'activeSection' => 'listings',
        ]);
    }

    #[On('openCreateListingModal')]
    public function openCreateListingModal(?string $mode = null): void
    {
        $this->resetListingForms();
        $this->createMode = $mode ?? 'existing_variant';
        $this->seedCreateDefaults();
        $this->showListingModal = true;
    }

    #[On('editListing')]
    public function editListing(int $listingId): void
    {
        $listing = $this->resolveOwnedListing($listingId, true);

        $this->resetListingForms();
        $this->editingListingId = $listing->id;
        $this->listingForm->setListing($listing);
        $this->listingForm->seller_id = $this->seller->id;
        $this->listingForm->status = $listing->status->value;
        $this->showListingModal = true;
    }

    public function saveListing(): void
    {
        try {
            DB::transaction(function (): void {
                if ($this->editingListingId !== null) {
                    $listing = $this->resolveOwnedListing($this->editingListingId, true);
                    $this->listingForm->listing = $listing;
                    $this->listingForm->seller_id = $this->seller->id;
                    $this->listingForm->status = $listing->status->value;
                    $this->productListingService->update($listing, $this->listingForm->validatedData());

                    return;
                }

                $this->listingForm->seller_id = $this->seller->id;

                match ($this->createMode) {
                    'existing_variant'         => $this->createListingFromExistingVariant(),
                    'existing_product_variant' => $this->createListingFromExistingProduct(),
                    'new_product'              => $this->createListingWithNewProduct(),
                    default                    => throw ValidationException::withMessages([
                        'createMode' => 'Cách tạo không hợp lệ.',
                    ]),
                };
            });

            $message = $this->editingListingId !== null
                ? 'Listing đã được cập nhật thành công.'
                : 'Listing đã được tạo thành công.';

            $this->showListingModal = false;
            $this->dispatch('pg:eventRefresh-sellerListingsTable');
            $this->dispatch('swal:success', ['message' => $message]);
            $this->resetListingForms();
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    #[On('openKeysModal')]
    public function openKeysModal(int $listingId): void
    {
        $listing = $this->resolveOwnedListing($listingId);

        $this->viewingKeysListingId = $listing->id;
        $this->keyCode = '';
        $this->showKeysModal = true;
    }

    public function saveKey(): void
    {
        try {
            $listing = $this->resolveOwnedListing((int) $this->viewingKeysListingId);

            $this->validate([
                'keyCode' => ['required', 'string', 'max:500'],
            ]);

            $this->productKeyService->create($listing, $this->keyCode, 'keyCode');
            $this->keyCode = '';

            $this->dispatch('pg:eventRefresh-sellerListingsTable');
            $this->dispatch('swal:success', ['message' => 'Key đã được tạo thành công.']);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->dispatch('swal:error', ['message' => $exception->getMessage()]);
        }
    }

    #[On('deleteKey')]
    public function deleteKey(int $keyId): void
    {
        try {
            $key = $this->productKeyRepository->findOwnedBySellerOrFail($this->seller->id, $keyId);

            $this->productKeyService->delete($key);
            $this->dispatch('pg:eventRefresh-sellerListingsTable');
            $this->dispatch('swal:success', ['message' => 'Key đã được xóa thành công.']);
        } catch (ValidationException $exception) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($exception)]);
        } catch (\Throwable $exception) {
            $this->dispatch('swal:error', ['message' => $exception->getMessage()]);
        }
    }

    protected function seedCreateDefaults(): void
    {
        $this->productForm->reset();
        $this->variantForm->resetForm();
        $this->listingForm->resetForm();

        $this->productForm->submitted_by_seller_id = $this->seller->id;
        $this->productForm->status = GeneralStatus::Inactive->value;
        $this->variantForm->status = ProductVariantStatus::Draft->value;
        $this->listingForm->status = ProductListingStatus::Draft->value;

        $this->listingForm->seller_id = $this->seller->id;
    }

    protected function resetListingForms(): void
    {
        $this->editingListingId = null;
        $this->selectedProductId = null;
        $this->selectedVariantId = null;
        $this->viewingKeysListingId = null;
        $this->keyCode = '';

        $this->productForm->reset();
        $this->variantForm->resetForm();
        $this->listingForm->resetForm();

        $this->productForm->submitted_by_seller_id = $this->seller->id;
        $this->productForm->status = GeneralStatus::Inactive->value;
        $this->variantForm->status = ProductVariantStatus::Draft->value;
        $this->listingForm->seller_id = $this->seller->id;
        $this->listingForm->status = ProductListingStatus::Draft->value;
    }

    protected function createListingFromExistingVariant(): string
    {
        $this->validate([
            'selectedVariantId'        => ['required', 'integer'],
            'listingForm.display_name' => ['nullable', 'string', 'max:255'],
            'listingForm.price'        => ['required', 'numeric', 'min:0'],
        ]);

        $variant = $this->resolveAllowedVariant((int) $this->selectedVariantId);

        $variant->loadMissing('product');

        $this->listingForm->variant_id = $variant->id;
        $this->listingForm->seller_id = $this->seller->id;
        $this->listingForm->status = $this->productListingService->defaultStatusForProduct($variant)->value;

        $this->productListingService->create($this->listingForm->validatedData(), 'listingForm.status');

        return 'Listing đã được tạo thành công.';
    }

    protected function createListingFromExistingProduct(): string
    {
        $this->validate([
            'selectedProductId'        => ['required', 'integer'],
            'variantForm.region_id'    => ['required', 'integer'],
            'variantForm.platform_id'  => ['required', 'integer'],
            'variantForm.os_id'        => ['required', 'integer'],
            'variantForm.edition'      => ['nullable', 'string', 'max:25'],
            'listingForm.display_name' => ['nullable', 'string', 'max:255'],
            'listingForm.price'        => ['required', 'numeric', 'min:0'],
        ]);

        $product = $this->resolveAllowedProduct($this->selectedProductId);

        $this->variantForm->product_id = $product->id;
        $this->variantForm->status = $this->productVariantService->defaultStatusForProduct($product)->value;

        $variant = $this->productVariantService->create($this->variantForm->validatedData(), 'variantForm.edition');

        $this->listingForm->variant_id = $variant->id;
        $this->listingForm->seller_id = $this->seller->id;
        $this->listingForm->status = $this->productListingService->defaultStatusForProduct($product)->value;
        $this->productListingService->create($this->listingForm->validatedData(), 'listingForm.status');

        return 'Listing đã được tạo thành công.';
    }

    protected function createListingWithNewProduct(): string
    {
        $this->validate([
            'productForm.name'         => ['required', 'string', 'max:255'],
            'productForm.slug'         => ['nullable', 'string', 'max:255'],
            'productForm.publisher'    => ['nullable', 'string', 'max:255'],
            'productForm.developer'    => ['nullable', 'string', 'max:255'],
            'productForm.release_date' => ['nullable', 'date_format:Y-m-d'],
            'productForm.description'  => ['nullable', 'string'],
            'productForm.status'       => ['required', 'integer'],
            'variantForm.region_id'    => ['required', 'integer'],
            'variantForm.platform_id'  => ['required', 'integer'],
            'variantForm.os_id'        => ['required', 'integer'],
            'variantForm.edition'      => ['nullable', 'string', 'max:25'],
            'listingForm.display_name' => ['nullable', 'string', 'max:255'],
            'listingForm.price'        => ['required', 'numeric', 'min:0'],
        ]);

        $this->productForm->submitted_by_seller_id = $this->seller->id;
        $this->productForm->status = GeneralStatus::Inactive->value;

        $product = $this->productService->create($this->productForm->validatedData(), 'productForm.slug');

        $this->variantForm->product_id = $product->id;
        $this->variantForm->status = $this->productVariantService->defaultStatusForProduct($product)->value;
        $variant = $this->productVariantService->create($this->variantForm->validatedData(), 'variantForm.edition');

        $this->listingForm->variant_id = $variant->id;
        $this->listingForm->seller_id = $this->seller->id;
        $this->listingForm->status = $this->productListingService->defaultStatusForProduct($product)->value;
        $this->productListingService->create($this->listingForm->validatedData(), 'listingForm.status');

        return 'Listing đã được tạo thành công.';
    }

    protected function resolveOwnedListing(int $listingId, bool $withTrashed = false): ProductListing
    {
        return $this->productListingRepository->findOwnedBySellerOrFail($this->seller->id, $listingId, $withTrashed);
    }

    protected function resolveAllowedProduct(?int $productId): Product
    {
        try {
            return $this->productRepository->findAccessibleForSellerOrFail($this->seller->id, (int) $productId);
        } catch (ModelNotFoundException) {
            throw ValidationException::withMessages([
                'selectedProductId' => 'Sản phẩm không hợp lệ hoặc không nằm trong danh mục khả dụng.',
            ]);
        }
    }

    protected function resolveAllowedVariant(int $variantId): ProductVariant
    {
        try {
            return $this->productVariantRepository->findAccessibleForSellerOrFail($this->seller->id, $variantId);
        } catch (ModelNotFoundException) {
            throw ValidationException::withMessages([
                'selectedVariantId' => 'Biến thể không hợp lệ hoặc không nằm trong danh mục khả dụng.',
            ]);
        }
    }

    protected function firstValidationMessage(ValidationException $exception): string
    {
        return collect($exception->errors())->flatten()->first() ?? $exception->getMessage();
    }
}
