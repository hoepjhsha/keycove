<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Product;

use App\Enums\GeneralStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\Cart;
use App\Models\Category;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\ProductListing;
use App\Models\Region;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Sản phẩm')]
class ProductIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $product = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: '')]
    public string $platform = '';

    #[Url(except: '')]
    public string $region = '';

    #[Url(except: '')]
    public string $os = '';

    #[Url(except: '')]
    public string $edition = '';

    #[Url(except: '')]
    public string $minPrice = '';

    #[Url(except: '')]
    public string $maxPrice = '';

    #[Url(except: false)]
    public bool $inStock = false;

    #[Url(except: 'newest')]
    public string $sortBy = 'newest';

    #[Url(except: 'grid')]
    public string $viewMode = 'grid';

    public bool $showMobileFilters = false;

    public int $perPage = 20;

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'product', 'category', 'platform', 'region', 'os', 'edition', 'minPrice', 'maxPrice', 'inStock', 'sortBy', 'viewMode'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'product', 'category', 'platform', 'region', 'os', 'edition', 'minPrice', 'maxPrice', 'inStock', 'sortBy']);
        $this->sortBy = 'newest';
        $this->showMobileFilters = false;
        $this->resetPage();
    }

    public function toggleMobileFilters(): void
    {
        $this->showMobileFilters = ! $this->showMobileFilters;
    }

    public function setViewMode(string $viewMode): void
    {
        if (! in_array($viewMode, ['grid', 'list'], true)) {
            return;
        }

        $this->viewMode = $viewMode;
        $this->resetPage();
    }

    public function addToCart(int $listingId)
    {
        $user = auth()->user();

        if ($user === null) {
            return redirect()->route('app.auth.login');
        }

        $listing = ProductListing::query()
            ->select('product_listings.*')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereNull('product_listings.seller_id')
            ->where('product_listings.status', ProductListingStatus::Active)
            ->where('product_variants.status', ProductVariantStatus::Active)
            ->where('products.status', GeneralStatus::Active)
            ->with(['variant.product'])
            ->findOrFail($listingId);

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $cartItem = $cart->items()->firstOrNew(['listing_id' => $listing->id]);
        $cartItem->quantity = $cartItem->exists ? $cartItem->quantity + 1 : 1;
        $cartItem->save();

        $product = $listing->variant?->product;
        $title = $listing->display_name ?: ($product?->name ?? 'Listing chưa có tên');

        $this->dispatch('shop:cart:add', item: [
            'id'         => $cartItem->id,
            'code'       => $cartItem->cart_item_code,
            'listing_id' => $listing->id,
            'title'      => $title,
            'subtitle'   => collect([$product?->name, $listing->variant?->edition])->filter()->implode(' • '),
            'quantity'   => (int) $cartItem->quantity,
            'price'      => (float) $listing->price,
            'stock'      => (int) $listing->stock_count,
            'url'        => route('app.products.show', ['product' => $product?->slug, 'listing' => $listing->slug]),
            'image'      => $product?->image_thumbnail_path ? StorageUtility::getUrl($product->image_thumbnail_path) : null,
        ]);
    }

    public function render(): View
    {
        $listings = $this->catalogQuery()->paginate($this->perPage);

        return view('pages.shop.product-index', [
            'listings'          => $listings,
            'categories'        => $this->categoryOptions(),
            'platforms'         => $this->platformOptions(),
            'regions'           => $this->regionOptions(),
            'operatingSystems'  => $this->operatingSystemOptions(),
            'sortOptions'       => $this->sortOptions(),
            'availableListings' => $listings->total(),
        ])->layout('components.layouts.shop');
    }

    public function paginationView(): string
    {
        return 'pages.shop.pagination';
    }

    protected function catalogQuery(): Builder
    {
        $hasDisplayNameColumn = Schema::hasColumn('product_listings', 'display_name');

        $query = ProductListing::query()
            ->select('product_listings.*')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereNull('product_listings.seller_id')
            ->where('product_listings.status', ProductListingStatus::Active)
            ->where('product_variants.status', ProductVariantStatus::Active)
            ->where('products.status', GeneralStatus::Active)
            ->with([
                'variant.product.categories',
                'variant.region',
                'variant.platform',
                'variant.operatingSystem',
            ])
            ->withCount([
                'keys as available_keys_count' => function (Builder $query): void {
                    $query->where('status', ProductKeyStatus::Available->value);
                },
            ]);

        $query->when($this->search !== '', function (Builder $query): void {
            $term = '%'.trim($this->search).'%';

            $query->where(function (Builder $subQuery) use ($term): void {
                if (Schema::hasColumn('product_listings', 'display_name')) {
                    $subQuery->where('product_listings.display_name', 'like', $term)
                        ->orWhere('products.name', 'like', $term);
                } else {
                    $subQuery->where('products.name', 'like', $term);
                }

                $subQuery->orWhere('products.name', 'like', $term)
                    ->orWhere('products.publisher', 'like', $term)
                    ->orWhere('products.developer', 'like', $term)
                    ->orWhere('product_variants.edition', 'like', $term);
            });
        });

        $query->when($this->product !== '', function (Builder $query): void {
            $query->where('products.slug', $this->product);
        });

        $query->when($this->category !== '', function (Builder $query): void {
            $query->whereHas('variant.product.categories', function (Builder $categoryQuery): void {
                $categoryQuery->where('slug', $this->category);
            });
        });

        $query->when($this->platform !== '', function (Builder $query): void {
            $query->whereHas('variant.platform', function (Builder $platformQuery): void {
                $platformQuery->where('slug', $this->platform);
            });
        });

        $query->when($this->region !== '', function (Builder $query): void {
            $query->whereHas('variant.region', function (Builder $regionQuery): void {
                $regionQuery->where('slug', $this->region);
            });
        });

        $query->when($this->os !== '', function (Builder $query): void {
            $query->whereHas('variant.operatingSystem', function (Builder $osQuery): void {
                $osQuery->where('slug', $this->os);
            });
        });

        $query->when($this->edition !== '', function (Builder $query): void {
            $query->where('product_variants.edition', 'like', '%'.trim($this->edition).'%');
        });

        $query->when($this->minPrice !== '', fn (Builder $query) => $query->where('product_listings.price', '>=', (float) $this->minPrice));
        $query->when($this->maxPrice !== '', fn (Builder $query) => $query->where('product_listings.price', '<=', (float) $this->maxPrice));

        if ($this->inStock) {
            $query->where('product_listings.stock_count', '>', 0);
        }

        return match ($this->sortBy) {
            'price_asc'  => $query->orderBy('product_listings.price')->orderByDesc('product_listings.created_at')->orderByDesc('product_listings.id'),
            'price_desc' => $query->orderByDesc('product_listings.price')->orderByDesc('product_listings.created_at')->orderByDesc('product_listings.id'),
            'name_asc'   => $hasDisplayNameColumn
                ? $query->orderByRaw('LOWER(COALESCE(NULLIF(product_listings.display_name, ""), products.name)) asc')->orderByDesc('product_listings.created_at')->orderByDesc('product_listings.id')
                : $query->orderByRaw('LOWER(products.name) asc')->orderByDesc('product_listings.created_at')->orderByDesc('product_listings.id'),
            'name_desc' => $hasDisplayNameColumn
                ? $query->orderByRaw('LOWER(COALESCE(NULLIF(product_listings.display_name, ""), products.name)) desc')->orderByDesc('product_listings.created_at')->orderByDesc('product_listings.id')
                : $query->orderByRaw('LOWER(products.name) desc')->orderByDesc('product_listings.created_at')->orderByDesc('product_listings.id'),
            default => $query->orderByDesc('product_listings.created_at')->orderByDesc('product_listings.id'),
        };
    }

    protected function categoryOptions()
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('status', GeneralStatus::Active)
            ->with(['children' => function ($query): void {
                $query->where('status', GeneralStatus::Active);
            }])
            ->orderBy('name')
            ->get()
            ->flatMap(function (Category $category) {
                $options = [
                    ['slug' => $category->slug, 'name' => $category->name],
                ];

                foreach ($category->children as $child) {
                    $options[] = [
                        'slug' => $child->slug,
                        'name' => $category->name.' / '.$child->name,
                    ];
                }

                return $options;
            });
    }

    protected function platformOptions()
    {
        return Platform::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['slug', 'name']);
    }

    protected function regionOptions()
    {
        return Region::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['slug', 'name']);
    }

    protected function operatingSystemOptions()
    {
        return OperatingSystem::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['slug', 'name']);
    }

    protected function sortOptions(): array
    {
        return [
            ['value' => 'newest', 'label' => 'Mới nhất trước'],
            ['value' => 'price_asc', 'label' => 'Giá: thấp đến cao'],
            ['value' => 'price_desc', 'label' => 'Giá: cao đến thấp'],
            ['value' => 'name_asc', 'label' => 'Tên: A-Z'],
            ['value' => 'name_desc', 'label' => 'Tên: Z-A'],
        ];
    }
}
