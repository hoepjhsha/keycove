<?php

declare(strict_types=1);

namespace App\Livewire\Shop\SellerBrowse;

use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\ProductListing;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Browse Sellers')]
class SellerIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'newest';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'sortBy'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'sortBy']);
        $this->sortBy = 'newest';
        $this->resetPage();
    }

    public function render(): View
    {
        $listings = $this->catalogQuery()->paginate(20);

        return view('pages.shop.seller-index', [
            'listings'          => $listings,
            'availableListings' => $listings->total(),
        ])->layout('components.layouts.shop');
    }

    protected function catalogQuery(): Builder
    {
        $query = ProductListing::query()
            ->select('product_listings.*')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereNotNull('product_listings.seller_id')
            ->where('product_listings.status', ProductListingStatus::Active)
            ->where('product_variants.status', ProductVariantStatus::Active)
            ->where('products.status', GeneralStatus::Active)
            ->with([
                'seller.user',
                'variant.product.categories',
                'variant.region',
                'variant.platform',
                'variant.operatingSystem',
            ]);

        $query->when($this->search !== '', function (Builder $query): void {
            $term = '%'.trim($this->search).'%';

            $query->where(function (Builder $subQuery) use ($term): void {
                $subQuery->where('product_listings.display_name', 'like', $term)
                    ->orWhere('product_listings.slug', 'like', $term)
                    ->orWhere('products.name', 'like', $term)
                    ->orWhere('products.publisher', 'like', $term)
                    ->orWhere('products.developer', 'like', $term)
                    ->orWhere('product_variants.edition', 'like', $term)
                    ->orWhereHas('seller', function (Builder $sellerQuery) use ($term): void {
                        $sellerQuery->where('shop_name', 'like', $term)
                            ->orWhereHas('user', function (Builder $userQuery) use ($term): void {
                                $userQuery->where('username', 'like', $term)
                                    ->orWhere('email', 'like', $term);
                            });
                    });
            });
        });

        return match ($this->sortBy) {
            'price_asc'  => $query->orderBy('product_listings.price')->orderByDesc('product_listings.created_at')->orderByDesc('product_listings.id'),
            'price_desc' => $query->orderByDesc('product_listings.price')->orderByDesc('product_listings.created_at')->orderByDesc('product_listings.id'),
            default      => $query->orderByDesc('product_listings.created_at')->orderByDesc('product_listings.id'),
        };
    }
}
