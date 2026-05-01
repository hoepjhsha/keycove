<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Abstracts\Controller;
use App\Managers\PaymentManager;
use App\Models\Cart;
use App\Services\Shop\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartCheckoutController extends Controller
{
    public function __invoke(Request $request, CheckoutService $checkoutService, PaymentManager $paymentManager): RedirectResponse
    {
        $user = auth()->user();

        abort_unless($user !== null, 403);

        $validated = $request->validate([
            'item_codes'   => ['required', 'array', 'min:1'],
            'item_codes.*' => ['string'],
        ]);

        $cart = Cart::query()
            ->with([
                'items.listing.variant.product',
                'items.listing.variant.region',
                'items.listing.variant.platform',
                'items.listing.variant.operatingSystem',
            ])
            ->firstOrCreate([
                'user_id' => $user->id,
            ]);

        $selectedCartItemCodes = array_values(array_unique(array_map('strval', $validated['item_codes'])));

        $selectedCartItemIds = $cart->items()
            ->whereIn('cart_item_code', $selectedCartItemCodes)
            ->pluck('id')
            ->all();

        abort_if(count($selectedCartItemIds) !== count($selectedCartItemCodes), 422, __('shop.checkout.selected_items_unavailable'));

        $order = $checkoutService->createOrderFromCart($cart, selectedCartItemIds: $selectedCartItemIds);

        $paymentUrl = $paymentManager->driver('vnpay')->createPayment([
            'txn_ref'    => $order->order_code,
            'amount'     => (float) $order->total_price,
            'order_info' => 'Thanh toán cho '.$order->order_code,
            'order_type' => 'other',
            'locale'     => 'vn',
        ]);

        return redirect()->away($paymentUrl);
    }
}
