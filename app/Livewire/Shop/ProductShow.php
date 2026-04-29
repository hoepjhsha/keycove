<?php

declare(strict_types=1);

namespace App\Livewire\Shop;

use App\Enums\GeneralStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\Product;
use App\Models\ProductListing;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Product Detail')]
class ProductShow extends Component
{
    public Product $product;

    public ProductListing $listing;

    public function mount(Product $product, ProductListing $listing): void
    {
        if ($listing->variant?->product_id !== $product->id) {
            abort(404);
        }

        if ($listing->seller_id !== null) {
            abort(404);
        }

        if ($product->trashed() || $listing->trashed() || $listing->variant?->trashed()) {
            abort(404);
        }

        if ($product->status !== GeneralStatus::Active || $listing->status !== ProductListingStatus::Active || $listing->variant?->status !== ProductVariantStatus::Active) {
            abort(404);
        }

        $this->product = $product->loadMissing(['categories', 'submittedBySeller.user']);
        $this->listing = $listing->loadMissing(['variant.region', 'variant.platform', 'variant.operatingSystem', 'variant.product.categories']);
        $this->listing->loadCount(['keys as available_keys_count' => function (Builder $query): void {
            $query->where('status', ProductKeyStatus::Available->value);
        }]);
    }

    public function render(): View
    {
        $relatedListings = ProductListing::query()
            ->select('product_listings.*')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereNull('product_listings.seller_id')
            ->where('product_listings.status', ProductListingStatus::Active)
            ->where('product_variants.status', ProductVariantStatus::Active)
            ->where('products.status', GeneralStatus::Active)
            ->where('products.id', $this->product->id)
            ->where('product_listings.id', '!=', $this->listing->id)
            ->with(['variant.product.categories', 'variant.region', 'variant.platform', 'variant.operatingSystem'])
            ->withCount(['keys as available_keys_count' => function ($query): void {
                $query->where('status', ProductKeyStatus::Available->value);
            }])
            ->orderByDesc('product_listings.created_at')
            ->limit(4)
            ->get();

        return view('pages.shop.product-show', [
            'relatedListings' => $relatedListings,
            'displayTitle'    => $this->listing->display_name ?: $this->product->name,
            'productImage'    => $this->product->image_thumbnail_path ? StorageUtility::getUrl($this->product->image_thumbnail_path) : null,
        ])->layout('components.layouts.shop');
    }
}
