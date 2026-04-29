<?php

declare(strict_types=1);

namespace App\Livewire\Shop;

use App\Enums\GeneralStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\Category;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\ProductListing;
use App\Models\Region;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Products')]
class ProductIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 0)]
    public int $categoryId = 0;

    #[Url(except: 0)]
    public int $platformId = 0;

    #[Url(except: 0)]
    public int $regionId = 0;

    #[Url(except: 0)]
    public int $osId = 0;

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

    public function updated($property): void
    {
        if (in_array($property, ['search', 'categoryId', 'platformId', 'regionId', 'osId', 'edition', 'minPrice', 'maxPrice', 'inStock', 'sortBy', 'viewMode'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'categoryId', 'platformId', 'regionId', 'osId', 'edition', 'minPrice', 'maxPrice', 'inStock', 'sortBy']);
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

        $query->when($this->categoryId > 0, function (Builder $query): void {
            $query->whereHas('variant.product.categories', function (Builder $categoryQuery): void {
                $categoryQuery->whereKey($this->categoryId);
            });
        });

        $query->when($this->platformId > 0, fn (Builder $query) => $query->where('product_variants.platform_id', $this->platformId));
        $query->when($this->regionId > 0, fn (Builder $query) => $query->where('product_variants.region_id', $this->regionId));
        $query->when($this->osId > 0, fn (Builder $query) => $query->where('product_variants.os_id', $this->osId));

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
                    ['id' => $category->id, 'name' => $category->name],
                ];

                foreach ($category->children as $child) {
                    $options[] = [
                        'id'   => $child->id,
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
            ->get(['id', 'name']);
    }

    protected function regionOptions()
    {
        return Region::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function operatingSystemOptions()
    {
        return OperatingSystem::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function sortOptions(): array
    {
        return [
            ['value' => 'newest', 'label' => 'Newest first'],
            ['value' => 'price_asc', 'label' => 'Price: low to high'],
            ['value' => 'price_desc', 'label' => 'Price: high to low'],
            ['value' => 'name_asc', 'label' => 'Name: A-Z'],
            ['value' => 'name_desc', 'label' => 'Name: Z-A'],
        ];
    }
}
