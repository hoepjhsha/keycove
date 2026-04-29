<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Abstracts\Controller;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Utilities\StorageUtility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $cartItem = $this->resolveOwnedCartItem($request, $cartItem);
        $requestedQuantity = (int) $request->validated('quantity');
        $maximumQuantity = max(1, (int) ($cartItem->listing?->stock_count ?? 1));

        $cartItem->quantity = min($requestedQuantity, $maximumQuantity);
        $cartItem->save();

        return response()->json([
            'item' => $this->itemPayload($cartItem->fresh(['cart', 'listing.variant.product'])),
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $cartItem = $this->resolveOwnedCartItem($request, $cartItem);
        $cart = $cartItem->cart;
        $itemId = $cartItem->id;

        $cartItem->delete();

        return response()->json([
            'item_id' => $itemId,
            'count'   => $this->cartCount($cart),
        ]);
    }

    protected function resolveOwnedCartItem(Request $request, CartItem $cartItem): CartItem
    {
        $cartItem->loadMissing(['cart', 'listing.variant.product']);

        abort_unless((int) $cartItem->cart?->user_id === (int) $request->user()?->id, 404);

        return $cartItem;
    }

    protected function itemPayload(CartItem $cartItem): array
    {
        $listing = $cartItem->listing;
        $product = $listing?->variant?->product;

        return [
            'id'         => $cartItem->id,
            'listing_id' => $listing?->id,
            'title'      => $listing?->display_name ?: ($product?->name ?? 'Unknown item'),
            'subtitle'   => collect([$product?->name, $listing?->variant?->edition])->filter()->implode(' • '),
            'quantity'   => (int) $cartItem->quantity,
            'price'      => (float) ($listing?->price ?? 0),
            'stock'      => (int) ($listing?->stock_count ?? 0),
            'url'        => $product !== null && $listing !== null
                ? route('app.products.show', ['product' => $product->slug, 'listing' => $listing->slug])
                : '#',
            'image' => $product?->image_thumbnail_path
                ? StorageUtility::getUrl($product->image_thumbnail_path)
                : null,
        ];
    }

    protected function cartCount(Cart $cart): int
    {
        return (int) $cart->items()->sum('quantity');
    }
}
