<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Seller;

use App\Enums\GeneralStatus;
use App\Enums\ProductKeyStatus;
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
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

        $listingsQuery = ProductListing::query()->where('seller_id', $seller->id);
        $productsQuery = Product::query()->where('submitted_by_seller_id', $seller->id);

        return [
            'listings'        => (clone $listingsQuery)->count(),
            'activeListings'  => (clone $listingsQuery)->where('status', ProductListingStatus::Active)->count(),
            'pendingListings' => (clone $listingsQuery)->where('status', ProductListingStatus::Pending)->count(),
            'products'        => (clone $productsQuery)->where('status', '!=', GeneralStatus::Deleted->value)->count(),
            'availableKeys'   => ProductKey::query()
                ->whereHas('listing', function (Builder $query) use ($seller): void {
                    $query->where('seller_id', $seller->id);
                })
                ->where('status', ProductKeyStatus::Available->value)
                ->count(),
        ];
    }

    /**
     * @return Collection<int, array{id:int, label:string, source:string, status:string}>
     */
    #[Computed]
    public function productOptions(): Collection
    {
        $seller = $this->seller;

        return Product::query()
            ->select(['id', 'name', 'submitted_by_seller_id', 'status'])
            ->where(function (Builder $query) use ($seller): void {
                $query->where(function (Builder $adminQuery): void {
                    $adminQuery->whereNull('submitted_by_seller_id')
                        ->where('status', GeneralStatus::Active);
                })->orWhere(function (Builder $sellerQuery) use ($seller): void {
                    $sellerQuery->where('submitted_by_seller_id', $seller->id)
                        ->where('status', '!=', GeneralStatus::Deleted->value);
                });
            })
            ->orderBy('name')
            ->get()
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
        return ProductVariant::query()
            ->select(['id', 'product_id', 'region_id', 'platform_id', 'os_id', 'edition', 'status'])
            ->where('status', '!=', ProductVariantStatus::Deleted->value)
            ->with([
                'product:id,name',
                'region:id,name',
                'platform:id,name',
                'operatingSystem:id,name',
            ])
            ->whereHas('product', function (Builder $query): void {
                $query->where('status', GeneralStatus::Active)
                    ->where(function (Builder $productQuery): void {
                        $productQuery->whereNull('submitted_by_seller_id')
                            ->orWhereNotNull('submitted_by_seller_id');
                    });
            })
            ->orderBy('product_id')
            ->orderBy('region_id')
            ->orderBy('platform_id')
            ->orderBy('os_id')
            ->get()
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

        return ProductListing::query()
            ->where('seller_id', $this->seller->id)
            ->with(['variant.product'])
            ->find($this->viewingKeysListingId);
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

        return ProductKey::query()
            ->where('listing_id', $this->viewingKeysListingId)
            ->orderByDesc('created_at')
            ->get();
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
        $this->showListingModal = true;
    }

    public function saveListing(): void
    {
        try {
            if ($this->editingListingId !== null) {
                $listing = $this->resolveOwnedListing($this->editingListingId, true);
                $this->listingForm->listing = $listing;
                $this->listingForm->seller_id = $this->seller->id;
                $this->listingForm->update();

                $message = 'Listing đã được cập nhật thành công.';
            } else {
                $this->listingForm->seller_id = $this->seller->id;

                $message = match ($this->createMode) {
                    'existing_variant' => $this->createListingFromExistingVariant(),
                    // 'existing_product_variant' => $this->createListingFromExistingProduct(),
                    // 'new_product'              => $this->createListingWithNewProduct(),
                    default => throw ValidationException::withMessages([
                        'createMode' => 'Cách tạo không hợp lệ.',
                    ]),
                };
            }

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
        $listing = $this->resolveOwnedListing((int) $this->viewingKeysListingId);

        $this->validate([
            'keyCode' => ['required', 'string', 'max:500'],
        ]);

        $keyCode = trim($this->keyCode);
        $keyHash = hash('sha256', $keyCode);

        if (ProductKey::query()->where('listing_id', $listing->id)->where('key_hash', $keyHash)->exists()) {
            throw ValidationException::withMessages([
                'keyCode' => 'Key này đã tồn tại trong listing đã chọn.',
            ]);
        }

        ProductKey::query()->create([
            'listing_id'    => $listing->id,
            'key_code'      => $keyCode,
            'key_hash'      => $keyHash,
            'status'        => ProductKeyStatus::Available->value,
            'order_item_id' => null,
        ]);

        $this->syncListingStockCount($listing);
        $this->keyCode = '';

        $this->dispatch('pg:eventRefresh-sellerListingsTable');
        $this->dispatch('swal:success', ['message' => 'Key đã được tạo thành công.']);
    }

    #[On('deleteKey')]
    public function deleteKey(int $keyId): void
    {
        try {
            $key = ProductKey::query()
                ->whereKey($keyId)
                ->whereHas('listing', function (Builder $query): void {
                    $query->where('seller_id', $this->seller->id);
                })
                ->firstOrFail();

            if ($key->status !== ProductKeyStatus::Available || $key->order_item_id !== null) {
                throw new \RuntimeException('Không thể xóa key không ở trạng thái khả dụng.');
            }

            $listingId = $key->listing_id;
            $key->delete();

            $this->syncListingStockCount($listingId);
            $this->dispatch('pg:eventRefresh-sellerListingsTable');
            $this->dispatch('swal:success', ['message' => 'Key đã được xóa thành công.']);
        } catch (\Throwable $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
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

        $this->listingForm->seller_id = $this->seller->id;
        $this->listingForm->status = ProductListingStatus::Pending->value;
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
        $this->listingForm->status = ProductListingStatus::Pending->value;
    }

    protected function createListingFromExistingVariant(): string
    {
        $this->validate([
            'selectedVariantId'        => ['required', 'integer'],
            'listingForm.display_name' => ['nullable', 'string', 'max:255'],
            'listingForm.price'        => ['required', 'numeric', 'min:0'],
            'listingForm.status'       => ['required', 'integer'],
        ]);

        $variant = ProductVariant::query()
            ->whereKey($this->selectedVariantId)
            ->where('status', '!=', ProductVariantStatus::Deleted->value)
            ->whereHas('product', function (Builder $query): void {
                $query->where('status', GeneralStatus::Active)
                    ->where(function (Builder $productQuery): void {
                        $productQuery->whereNull('submitted_by_seller_id')
                            ->orWhere('submitted_by_seller_id', $this->seller->id);
                    });
            })
            ->firstOrFail();

        $this->listingForm->variant_id = $variant->id;

        $this->listingForm->store();

        return 'Listing đã được tạo thành công.';
    }

    /*
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
            'listingForm.status'       => ['required', 'integer'],
        ]);

        $product = $this->resolveOwnedProduct($this->selectedProductId);

        $this->variantForm->product_id = $product->id;
        $this->variantForm->status = ProductVariantStatus::Draft->value;

        $variant = $this->variantForm->store();

        $this->listingForm->variant_id = $variant->id;
        $this->listingForm->store();

        return 'Listing đã được tạo thành công.';
    }
    */

    /*
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
            'listingForm.status'       => ['required', 'integer'],
        ]);

        $this->productForm->submitted_by_seller_id = $this->seller->id;

        $product = $this->productForm->store();

        $this->variantForm->product_id = $product->id;
        $this->variantForm->status = ProductVariantStatus::Draft->value;
        $variant = $this->variantForm->store();

        $this->listingForm->variant_id = $variant->id;
        $this->listingForm->seller_id = $this->seller->id;
        $this->listingForm->store();

        return 'Listing đã được tạo thành công.';
    }
    */

    protected function resolveOwnedListing(int $listingId, bool $withTrashed = false): ProductListing
    {
        $query = ProductListing::query()->where('seller_id', $this->seller->id);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($listingId);
    }

    protected function resolveOwnedProduct(int $productId): Product
    {
        return Product::query()
            ->whereKey($productId)
            ->where(function (Builder $query): void {
                $query->whereNull('submitted_by_seller_id')
                    ->orWhere('submitted_by_seller_id', $this->seller->id);
            })
            ->where('status', '!=', GeneralStatus::Deleted->value)
            ->firstOrFail();
    }

    protected function syncListingStockCount(ProductListing|int $listing): void
    {
        $listing = $listing instanceof ProductListing
            ? $listing
            : $this->resolveOwnedListing($listing, true);

        $listing->forceFill([
            'stock_count' => $listing->keys()->where('status', ProductKeyStatus::Available->value)->count(),
        ])->save();
    }
}
