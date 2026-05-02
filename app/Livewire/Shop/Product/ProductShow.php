<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Product;

use App\Enums\GeneralStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\Review;
use App\Models\User;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Chi tiết sản phẩm')]
class ProductShow extends Component
{
    public Product $product;

    public ProductListing $listing;

    public function mount(Product $product, ProductListing $listing): void
    {
        if ($listing->variant?->product_id !== $product->id) {
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
            ->where('product_listings.status', ProductListingStatus::Active)
            ->where('product_variants.status', ProductVariantStatus::Active)
            ->where('products.status', GeneralStatus::Active)
            ->with(['variant.product'])
            ->findOrFail($listingId);

        if ($this->currentSellerId() !== null && $this->currentSellerId() === $listing->seller_id) {
            session()->flash('seller-status', 'Bạn không thể mua listing của chính mình.');

            return;
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $cartItem = $cart->items()->firstOrNew(['listing_id' => $listing->id]);

        if ((int) $listing->stock_count < 1) {
            session()->flash('seller-status', __('shop.checkout.item_out_of_stock'));

            return;
        }

        if ($cartItem->exists && (int) $cartItem->quantity >= (int) $listing->stock_count) {
            session()->flash('seller-status', __('shop.checkout.insufficient_available_keys'));

            return;
        }

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
        $currentSellerId = $this->currentSellerId();

        $reviewQuery = Review::query()
            ->with(['user'])
            ->whereHas('orderItem', function (Builder $query): void {
                $query->where('listing_id', $this->listing->id);
            });

        $productReviewCount = (clone $reviewQuery)->count();
        $productReviewAverage = (float) ((clone $reviewQuery)->avg('rating') ?? 0);
        $productReviews = (clone $reviewQuery)
            ->latest('reviews.created_at')
            ->limit(8)
            ->get()
            ->map(function (Review $review): array {
                return [
                    'id'         => $review->id,
                    'user_name'  => $review->user?->username ?? 'Người mua',
                    'rating'     => $review->rating,
                    'comment'    => $review->comment,
                    'created_at' => $review->created_at?->format('d/m/Y H:i'),
                    'media'      => collect($review->media ?? [])
                        ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
                        ->map(fn (string $path): array => [
                            'label' => Str::afterLast($path, '/'),
                            'url'   => StorageUtility::getUrl($path),
                        ])
                        ->values()
                        ->all(),
                ];
            });

        $relatedListings = ProductListing::query()
            ->select('product_listings.*')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('product_listings.status', ProductListingStatus::Active)
            ->where('product_variants.status', ProductVariantStatus::Active)
            ->where('products.status', GeneralStatus::Active)
            ->where('products.id', $this->product->id)
            ->where('product_listings.id', '!=', $this->listing->id)
            ->when($currentSellerId !== null, function (Builder $query) use ($currentSellerId): void {
                $query->where(function (Builder $sellerQuery) use ($currentSellerId): void {
                    $sellerQuery->whereNull('product_listings.seller_id')
                        ->orWhere('product_listings.seller_id', '!=', $currentSellerId);
                });
            })
            ->with(['variant.product.categories', 'variant.region', 'variant.platform', 'variant.operatingSystem'])
            ->withCount(['keys as available_keys_count' => function ($query): void {
                $query->where('status', ProductKeyStatus::Available->value);
            }])
            ->orderByDesc('product_listings.created_at')
            ->limit(4)
            ->get();

        return view('pages.shop.product-show', [
            'relatedListings'      => $relatedListings,
            'displayTitle'         => $this->listing->display_name ?: $this->product->name,
            'productImage'         => $this->product->image_thumbnail_path ? StorageUtility::getUrl($this->product->image_thumbnail_path) : null,
            'productReviewCount'   => $productReviewCount,
            'productReviewAverage' => $productReviewAverage,
            'productReviews'       => $productReviews,
            'canAddToCart'         => $this->canAddToCart(),
        ])->layout('components.layouts.shop');
    }

    protected function currentSellerId(): ?int
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        $user->loadMissing('seller');

        return $user->seller?->id;
    }

    protected function canAddToCart(): bool
    {
        $currentSellerId = $this->currentSellerId();

        if ((int) $this->listing->stock_count < 1) {
            return false;
        }

        if ($currentSellerId === null) {
            return true;
        }

        return $currentSellerId !== $this->listing->seller_id;
    }
}
