<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Seller;

use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
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
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
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

    public bool $showProductModal = false;

    public bool $showVariantModal = false;

    public ?int $editingProductId = null;

    public ?int $editingVariantId = null;

    public ?int $variantProductId = null;

    public ProductCreateForm $createForm;

    public ProductEditForm $editForm;

    public ProductVariantForm $variantForm;

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
        return Product::query()
            ->withTrashed()
            ->where('submitted_by_seller_id', $this->seller->id)
            ->with([
                'variants' => function ($query): void {
                    $query
                        ->withTrashed()
                        ->with([
                            'region:id,name',
                            'platform:id,name',
                            'operatingSystem:id,name',
                        ])
                        ->withCount('listings')
                        ->orderBy('region_id')
                        ->orderBy('platform_id')
                        ->orderBy('os_id');
                },
            ])
            ->withCount(['variants', 'listings'])
            ->orderByDesc('created_at')
            ->get();
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
        $query = Product::query()->where('submitted_by_seller_id', $this->seller->id)->withoutTrashed();

        return [
            'products'        => (clone $query)->count(),
            'pendingProducts' => (clone $query)->where('status', GeneralStatus::Inactive)->count(),
            'activeProducts'  => (clone $query)->where('status', GeneralStatus::Active)->count(),
            'variants'        => ProductVariant::query()
                ->whereHas('product', function (Builder $builder): void {
                    $builder->where('submitted_by_seller_id', $this->seller->id);
                })
                ->count(),
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
                $this->editForm->update();
            } else {
                $this->createForm->submitted_by_seller_id = $this->seller->id;
                $this->createForm->status = GeneralStatus::Inactive->value;
                $this->createForm->store();
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
        $this->variantForm->status = $product->status === GeneralStatus::Active
            ? ProductVariantStatus::Active->value
            : ProductVariantStatus::Draft->value;
        $this->showVariantModal = true;
    }

    public function openEditVariantModal(int $variantId): void
    {
        $variant = $this->resolveOwnedVariant($variantId, true);

        $this->resetVariantForm();
        $this->editingVariantId = $variant->id;
        $this->variantProductId = $variant->product_id;
        $this->variantForm->setVariant($variant);

        if ($variant->product?->status !== GeneralStatus::Active) {
            $this->variantForm->status = ProductVariantStatus::Draft->value;
        }

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
            DB::transaction(function () use ($id): void {
                $product = $this->resolveOwnedProduct($id, true);

                $product->status = GeneralStatus::Deleted;
                $product->save();
                $product->delete();
            });

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
            DB::transaction(function () use ($id): void {
                $product = $this->resolveOwnedProduct($id, true);

                $product->restore();
                $product->status = GeneralStatus::Inactive;
                $product->save();
            });

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
            DB::transaction(function () use ($id): void {
                $variant = ProductVariant::query()
                    ->whereHas('product', function (Builder $query): void {
                        $query->where('submitted_by_seller_id', $this->seller->id);
                    })
                    ->findOrFail($id);

                if ($variant->listings()->where('status', '!=', ProductListingStatus::Deleted->value)->exists()) {
                    throw new \RuntimeException('Không thể xóa variant có listing chưa bị xóa.');
                }

                $variant->status = ProductVariantStatus::Deleted;
                $variant->save();
                $variant->delete();
            });

            $this->dispatch('swal:success', ['message' => 'Variant đã được xóa thành công.']);
        } catch (ModelNotFoundException $e) {
            $this->dispatch('swal:error', ['message' => 'Variant không tồn tại hoặc không thuộc quyền quản lý của bạn.']);
        } catch (\Throwable $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    #[On('performRestoreVariant')]
    public function performRestoreVariant(int $id): void
    {
        try {
            DB::transaction(function () use ($id): void {
                $variant = $this->resolveOwnedVariant($id, true);

                $variant->restore();
                $variant->loadMissing(['product' => function (BelongsTo $query): void {
                    $query->withTrashed();
                }]);
                $variant->status = $variant->product?->status === GeneralStatus::Active
                    ? ProductVariantStatus::Hidden
                    : ProductVariantStatus::Draft;
                $variant->save();
            });

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
            $variant->loadMissing('product');

            if ($variant->product?->status !== GeneralStatus::Active) {
                return;
            }

            if (! in_array($variant->status, [ProductVariantStatus::Active, ProductVariantStatus::Hidden], true)) {
                return;
            }

            $variant->status = match ($variant->status) {
                ProductVariantStatus::Active => ProductVariantStatus::Hidden,
                ProductVariantStatus::Hidden => ProductVariantStatus::Active,
                default                      => $variant->status,
            };

            $variant->save();

            $this->dispatch('swal:success', ['message' => 'Trạng thái variant đã được cập nhật.']);
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
                $this->variantForm->status = $product->status === GeneralStatus::Active
                    && in_array($variant->status, [ProductVariantStatus::Active, ProductVariantStatus::Hidden], true)
                    ? $variant->status->value
                    : ProductVariantStatus::Draft->value;
                $this->variantForm->update();
            } else {
                $this->variantForm->status = $product->status === GeneralStatus::Active
                    ? ProductVariantStatus::Active->value
                    : ProductVariantStatus::Draft->value;
                $this->variantForm->store();
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
        $query = ProductVariant::query()->whereHas('product', function (Builder $builder): void {
            $builder->withTrashed()->where('submitted_by_seller_id', $this->seller->id);
        });

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($variantId);
    }

    protected function resolveOwnedProduct(int $productId, bool $withTrashed = false): Product
    {
        $query = Product::query()->where('submitted_by_seller_id', $this->seller->id);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($productId);
    }

    public function productImageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return StorageUtility::getUrl($path);
    }
}
