<?php

declare(strict_types=1);

namespace App\Livewire\Shop;

use App\Enums\GeneralStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\SystemConfig;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Cửa hàng')]
class Home extends Component
{
    public bool $showAllCategories = false;

    public function render(): View
    {
        $featuredLimit = $this->featuredLimit();

        return view('pages.landing.shop', [
            'categories'       => $this->categoryCards(),
            'featuredProducts' => $this->featuredProducts($featuredLimit),
            'sellerListings'   => $this->sellerListings(8),
        ])->layout('components.layouts.shop');
    }

    public function toggleCategories(): void
    {
        $this->showAllCategories = ! $this->showAllCategories;
    }

    protected function featuredLimit(): int
    {
        $configuredValue = SystemConfig::query()
            ->where('key', 'featured_products_count')
            ->value('value');

        $featuredLimit = (int) ($configuredValue ?: 8);

        return $featuredLimit > 0 ? $featuredLimit : 8;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function categoryCards(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('status', GeneralStatus::Active)
            ->with(['children' => function ($query): void {
                $query->where('status', GeneralStatus::Active)->orderBy('name');
            }])
            ->withCount(['products as active_products_count' => function (Builder $query): void {
                $query->where('products.status', GeneralStatus::Active);
            }])
            ->orderByDesc('active_products_count')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category): array {
                return [
                    'id'             => $category->id,
                    'name'           => $category->name,
                    'slug'           => $category->slug,
                    'products_count' => (int) $category->active_products_count,
                    'children'       => $category->children->map(fn (Category $child): array => [
                        'id'   => $child->id,
                        'name' => $child->name,
                    ])->values(),
                ];
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function featuredProducts(int $limit): Collection
    {
        $sales = OrderItem::query()
            ->selectRaw('products.id as product_id, SUM(order_items.quantity) as sold_quantity')
            ->join('product_listings', 'product_listings.id', '=', 'order_items.listing_id')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereNull('product_listings.seller_id')
            ->where('order_items.status', OrderStatus::Completed->value)
            ->where('product_listings.status', ProductListingStatus::Active)
            ->where('product_variants.status', ProductVariantStatus::Active)
            ->where('products.status', GeneralStatus::Active)
            ->groupBy('products.id')
            ->orderByDesc('sold_quantity')
            ->limit($limit)
            ->get();

        $orderedProductIds = $sales->pluck('product_id')->map(fn (mixed $productId): int => (int) $productId)->all();

        $products = $orderedProductIds === []
            ? collect()
            : Product::query()
                ->select(['id', 'name', 'slug', 'image_thumbnail_path', 'publisher', 'developer'])
                ->whereIn('id', $orderedProductIds)
                ->where('status', GeneralStatus::Active)
                ->with(['categories' => function ($query): void {
                    $query->orderBy('name');
                }])
                ->get()
                ->sortBy(fn (Product $product): int => array_search($product->id, $orderedProductIds, true) ?: 0)
                ->values();

        $remaining = $limit - $products->count();

        if ($remaining > 0) {
            $excludedIds = $products->pluck('id')->all();

            $fallbackProducts = Product::query()
                ->select(['id', 'name', 'slug', 'image_thumbnail_path', 'publisher', 'developer'])
                ->where('status', GeneralStatus::Active)
                ->whereHas('variants', function (Builder $variantQuery): void {
                    $variantQuery->where('status', ProductVariantStatus::Active)
                        ->whereHas('listings', function (Builder $listingQuery): void {
                            $listingQuery->whereNull('seller_id')
                                ->where('status', ProductListingStatus::Active);
                        });
                })
                ->when($excludedIds !== [], function (Builder $query) use ($excludedIds): void {
                    $query->whereNotIn('id', $excludedIds);
                })
                ->with(['categories' => function ($query): void {
                    $query->orderBy('name');
                }])
                ->inRandomOrder()
                ->limit($remaining)
                ->get();

            $products = $products->concat($fallbackProducts)->values();
        }

        $productIds = $products->pluck('id')->all();

        if ($productIds === []) {
            return collect();
        }

        $representativeListings = ProductListing::query()
            ->select('product_listings.*', 'product_variants.product_id')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->whereNull('product_listings.seller_id')
            ->whereIn('product_variants.product_id', $productIds)
            ->where('product_listings.status', ProductListingStatus::Active)
            ->where('product_variants.status', ProductVariantStatus::Active)
            ->with(['variant.product.categories', 'variant.region', 'variant.platform', 'variant.operatingSystem'])
            ->orderByDesc('product_listings.created_at')
            ->get()
            ->groupBy('product_id')
            ->map(fn (Collection $listings): ProductListing => $listings->first())
            ->all();

        return $products->map(function (Product $product) use ($representativeListings): array {
            $listing = $representativeListings[$product->id] ?? null;
            $categories = $product->categories;
            $productCategories = $categories->take(3)->map(fn (Category $category): array => [
                'id'   => $category->id,
                'name' => $category->name,
            ])->values();

            return [
                'id'        => $product->id,
                'name'      => $product->name,
                'slug'      => $product->slug,
                'publisher' => $product->publisher,
                'developer' => $product->developer,
                'image'     => $product->image_thumbnail_path ? StorageUtility::getUrl($product->image_thumbnail_path) : null,
                'listing'   => $listing ? [
                    'id'          => $listing->id,
                    'slug'        => $listing->slug,
                    'title'       => $listing->display_name ?: $product->name,
                    'price'       => (float) $listing->price,
                    'stock_count' => (int) $listing->stock_count,
                ] : null,
                'categories' => $productCategories,
                'url'        => route('app.products.index', ['product' => $product->slug]),
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function sellerListings(int $limit): Collection
    {
        $currentSellerId = auth()->user()?->loadMissing('seller')?->seller?->id;

        return ProductListing::query()
            ->select('product_listings.*')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereNotNull('product_listings.seller_id')
            ->where('product_listings.status', ProductListingStatus::Active)
            ->where('product_variants.status', ProductVariantStatus::Active)
            ->where('products.status', GeneralStatus::Active)
            ->when($currentSellerId !== null, function (Builder $query) use ($currentSellerId): void {
                $query->where('product_listings.seller_id', '!=', $currentSellerId);
            })
            ->with([
                'seller.user',
                'variant.product.categories',
                'variant.region',
                'variant.platform',
                'variant.operatingSystem',
            ])
            ->orderByDesc('product_listings.created_at')
            ->limit($limit)
            ->get()
            ->map(function (ProductListing $listing): array {
                $product = $listing->variant?->product;
                $categories = $product?->categories?->take(2)->map(fn (Category $category): array => [
                    'id'   => $category->id,
                    'name' => $category->name,
                ])->values() ?? collect();

                return [
                    'id'           => $listing->id,
                    'title'        => $listing->display_name ?: ($product?->name ?? 'Listing chưa có tên'),
                    'slug'         => $listing->slug,
                    'price'        => (float) $listing->price,
                    'stock_count'  => (int) $listing->stock_count,
                    'seller_name'  => $listing->seller?->shop_name ?: $listing->seller?->user?->username ?: 'Người bán',
                    'product_name' => $product?->name ?? 'Sản phẩm chưa xác định',
                    'product_slug' => $product?->slug,
                    'image'        => $product?->image_thumbnail_path ? StorageUtility::getUrl($product->image_thumbnail_path) : null,
                    'categories'   => $categories,
                ];
            });
    }
}
