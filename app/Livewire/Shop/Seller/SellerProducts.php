<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Seller;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Form\Product\ProductCreateForm;
use App\Livewire\Admin\Form\Product\ProductEditForm;
use App\Livewire\Admin\Form\Product\ProductVariantForm;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use App\Models\User;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Sản phẩm của tôi')]
class SellerProducts extends Component
{
    use WithFileUploads;

    protected ProductRepositoryInterface $productRepository;

    protected ProductVariantRepositoryInterface $productVariantRepository;

    protected ProductService $productService;

    protected ProductVariantService $productVariantService;

    public bool $showProductModal = false;

    public bool $showVariantModal = false;

    public ?int $editingProductId = null;

    public ?int $editingVariantId = null;

    public ?int $variantProductId = null;

    public ProductCreateForm $createForm;

    public ProductEditForm $editForm;

    public ProductVariantForm $variantForm;

    public function boot(
        ProductRepositoryInterface $productRepository,
        ProductVariantRepositoryInterface $productVariantRepository,
        ProductService $productService,
        ProductVariantService $productVariantService,
    ): void {
        $this->productRepository = $productRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->productService = $productService;
        $this->productVariantService = $productVariantService;
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

    /**
     * @return EloquentCollection<int, Product>
     */
    #[Computed]
    public function products(): EloquentCollection
    {
        return $this->productRepository->getSellerProductsWithVariants($this->seller->id);
    }

    public function render(): View
    {
        return view('pages.shop.seller.products', [
            'seller'  => $this->seller,
            'metrics' => $this->metrics(),
        ])->layout('components.layouts.seller', [
            'title'         => 'Sản phẩm của tôi',
            'user'          => auth()->user(),
            'seller'        => $this->seller,
            'activeSection' => 'products',
        ]);
    }

    public function metrics(): array
    {
        return [
            'products'        => $this->productRepository->countOwnedBySeller($this->seller->id),
            'pendingProducts' => $this->productRepository->countOwnedBySeller($this->seller->id, GeneralStatus::Inactive),
            'activeProducts'  => $this->productRepository->countOwnedBySeller($this->seller->id, GeneralStatus::Active),
            'variants'        => $this->productVariantRepository->countOwnedBySeller($this->seller->id),
        ];
    }

    /**
     * @return EloquentCollection<int, Region>
     */
    #[Computed]
    public function regions(): EloquentCollection
    {
        return Region::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return EloquentCollection<int, Platform>
     */
    #[Computed]
    public function platforms(): EloquentCollection
    {
        return Platform::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return EloquentCollection<int, OperatingSystem>
     */
    #[Computed]
    public function operatingSystems(): EloquentCollection
    {
        return OperatingSystem::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get();
    }

    public function openCreateProductModal(): void
    {
        $this->resetProductForms();
        $this->showProductModal = true;
    }

    public function openEditProductModal(int $productId): void
    {
        $product = $this->resolveOwnedProduct($productId, true);

        if ($product->status !== GeneralStatus::Inactive) {
            throw ValidationException::withMessages([
                'product' => 'Sản phẩm đã duyệt không thể chỉnh sửa.',
            ]);
        }

        $this->resetProductForms();
        $this->editingProductId = $product->id;
        $this->editForm->setProduct($product);
        $this->editForm->status = GeneralStatus::Inactive->value;
        $this->showProductModal = true;
    }

    public function saveProduct(): void
    {
        try {
            if ($this->editingProductId !== null) {
                $product = $this->resolveOwnedProduct($this->editingProductId, true);

                if ($product->status !== GeneralStatus::Inactive) {
                    throw ValidationException::withMessages([
                        'product' => 'Sản phẩm đã duyệt không thể chỉnh sửa.',
                    ]);
                }

                $this->editForm->status = GeneralStatus::Inactive->value;
                $this->productService->update(
                    $product,
                    $this->editForm->validatedData(),
                    'editForm.slug',
                    'editForm.status',
                );
            } else {
                $this->createForm->submitted_by_seller_id = $this->seller->id;
                $this->createForm->status = GeneralStatus::Inactive->value;
                $this->productService->create($this->createForm->validatedData(), 'createForm.slug');
            }

            $this->showProductModal = false;
            $this->dispatch('swal:success', [
                'message' => $this->editingProductId !== null
                    ? 'Sản phẩm đã được cập nhật thành công.'
                    : 'Sản phẩm đã được tạo thành công.',
            ]);
            $this->resetProductForms();
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function openVariantModal(int $productId): void
    {
        $product = $this->resolveOwnedProduct($productId);

        $this->resetVariantForm();
        $this->variantProductId = $product->id;
        $this->variantForm->product_id = $product->id;
        $this->variantForm->status = $this->productVariantService->defaultStatusForProduct($product)->value;
        $this->showVariantModal = true;
    }

    public function openEditVariantModal(int $variantId): void
    {
        $variant = $this->resolveOwnedVariant($variantId, true);
        $variant->loadMissing(['product' => fn ($query) => $query->withTrashed()]);

        $this->resetVariantForm();
        $this->editingVariantId = $variant->id;
        $this->variantProductId = $variant->product_id;
        $this->variantForm->setVariant($variant);

        $this->variantForm->status = $this->productVariantService
            ->editableStatusForSeller($variant->product, $variant)
            ->value;

        $this->showVariantModal = true;
    }

    public function deleteProduct(int $productId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Xóa product?',
            'text'   => 'Bạn có chắc muốn xóa product này?',
            'method' => 'performDeleteProduct',
            'id'     => $productId,
        ]);
    }

    public function restoreProduct(int $productId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Khôi phục product?',
            'text'   => 'Bạn có chắc muốn khôi phục product này?',
            'method' => 'performRestoreProduct',
            'id'     => $productId,
        ]);
    }

    #[On('performDeleteProduct')]
    public function performDeleteProduct(int $id): void
    {
        try {
            $this->productService->delete($this->resolveOwnedProduct($id, true));

            $this->dispatch('swal:success', ['message' => 'Product đã được xóa thành công.']);
        } catch (ModelNotFoundException $e) {
            $this->dispatch('swal:error', ['message' => 'Product không tồn tại hoặc không thuộc quyền quản lý của bạn.']);
        } catch (\Throwable $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    #[On('performRestoreProduct')]
    public function performRestoreProduct(int $id): void
    {
        try {
            $this->productService->restore($this->resolveOwnedProduct($id, true));

            $this->dispatch('swal:success', ['message' => 'Product đã được khôi phục thành công.']);
        } catch (ModelNotFoundException $e) {
            $this->dispatch('swal:error', ['message' => 'Product không tồn tại hoặc không thuộc quyền quản lý của bạn.']);
        } catch (\Throwable $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function deleteVariant(int $variantId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Xóa variant?',
            'text'   => 'Bạn có chắc muốn xóa variant này?',
            'method' => 'performDeleteVariant',
            'id'     => $variantId,
        ]);
    }

    public function restoreVariant(int $variantId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Khôi phục variant?',
            'text'   => 'Bạn có chắc muốn khôi phục variant này?',
            'method' => 'performRestoreVariant',
            'id'     => $variantId,
        ]);
    }

    public function toggleVariantStatus(int $variantId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Đổi trạng thái variant?',
            'text'   => 'Bạn có chắc muốn đổi trạng thái variant này?',
            'method' => 'performToggleVariantStatus',
            'id'     => $variantId,
        ]);
    }

    #[On('performDeleteVariant')]
    public function performDeleteVariant(int $id): void
    {
        try {
            $this->productVariantService->delete($this->resolveOwnedVariant($id, true), true);

            $this->dispatch('swal:success', ['message' => 'Variant đã được xóa thành công.']);
        } catch (ModelNotFoundException $e) {
            $this->dispatch('swal:error', ['message' => 'Variant không tồn tại hoặc không thuộc quyền quản lý của bạn.']);
        } catch (ValidationException $e) {
            $this->dispatch('swal:error', ['message' => $this->firstValidationMessage($e)]);
        } catch (\Throwable $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    #[On('performRestoreVariant')]
    public function performRestoreVariant(int $id): void
    {
        try {
            $variant = $this->resolveOwnedVariant($id, true);
            $variant->loadMissing(['product' => fn ($query) => $query->withTrashed()]);

            $this->productVariantService->restore(
                $variant,
                $this->productVariantService->restoredStatusForProduct($variant->product),
            );

            $this->dispatch('swal:success', ['message' => 'Variant đã được khôi phục thành công.']);
        } catch (ModelNotFoundException $e) {
            $this->dispatch('swal:error', ['message' => 'Variant không tồn tại hoặc không thuộc quyền quản lý của bạn.']);
        } catch (\Throwable $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    #[On('performToggleVariantStatus')]
    public function performToggleVariantStatus(int $id): void
    {
        try {
            $variant = $this->resolveOwnedVariant($id);

            if ($this->productVariantService->toggleVisibility($variant)) {
                $this->dispatch('swal:success', ['message' => 'Trạng thái variant đã được cập nhật.']);
            }
        } catch (ModelNotFoundException $e) {
            $this->dispatch('swal:error', ['message' => 'Variant không tồn tại hoặc không thuộc quyền quản lý của bạn.']);
        } catch (\Throwable $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    public function saveVariant(): void
    {
        try {
            $product = $this->resolveOwnedProduct((int) $this->variantForm->product_id);
            $this->variantForm->product_id = $product->id;

            if ($this->editingVariantId !== null) {
                $variant = $this->resolveOwnedVariant($this->editingVariantId);
                $this->variantForm->variant = $variant;
                $this->variantForm->status = $this->productVariantService
                    ->editableStatusForSeller($product, $variant)
                    ->value;

                $this->productVariantService->update(
                    $variant,
                    $this->variantForm->validatedData(),
                    'variantForm.edition',
                    'variantForm.status',
                );
            } else {
                $this->variantForm->status = $this->productVariantService->defaultStatusForProduct($product)->value;
                $this->productVariantService->create($this->variantForm->validatedData(), 'variantForm.edition');
            }

            $this->showVariantModal = false;
            $this->dispatch('swal:success', [
                'message' => $this->editingVariantId !== null
                    ? 'Biến thể đã được cập nhật thành công.'
                    : 'Biến thể đã được tạo thành công.',
            ]);
            $this->resetVariantForm();
        } catch (ModelNotFoundException $e) {
            throw ValidationException::withMessages([
                'product' => 'Sản phẩm không hợp lệ hoặc không thuộc quyền quản lý của bạn.',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    protected function resetProductForms(): void
    {
        $this->editingProductId = null;

        $this->createForm->reset();

        $this->createForm->submitted_by_seller_id = $this->seller->id;
        $this->createForm->status = GeneralStatus::Inactive->value;

        $this->editForm->product = null;
        $this->editForm->name = '';
        $this->editForm->slug = '';
        $this->editForm->publisher = null;
        $this->editForm->developer = null;
        $this->editForm->release_date = null;
        $this->editForm->description = null;
        $this->editForm->status = GeneralStatus::Inactive->value;
        $this->editForm->categories = [];
        $this->editForm->image = null;
        $this->editForm->systemRequirements = [['key' => '', 'value' => '']];

        $this->editForm->status = GeneralStatus::Inactive->value;
    }

    protected function resetVariantForm(): void
    {
        $this->editingVariantId = null;
        $this->variantProductId = null;
        $this->variantForm->resetForm();
    }

    protected function resolveOwnedVariant(int $variantId, bool $withTrashed = false): ProductVariant
    {
        return $this->productVariantRepository->findOwnedBySellerOrFail($this->seller->id, $variantId, $withTrashed);
    }

    protected function resolveOwnedProduct(int $productId, bool $withTrashed = false): Product
    {
        return $this->productRepository->findOwnedBySellerOrFail($this->seller->id, $productId, $withTrashed);
    }

    public function productImageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return StorageUtility::getUrl($path);
    }

    protected function firstValidationMessage(ValidationException $exception): string
    {
        return collect($exception->errors())->flatten()->first() ?? $exception->getMessage();
    }
}
