<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Checkout;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Thanh toán')]
class CheckoutReview extends Component
{
    /**
     * @var list<string>
     */
    public array $selectedCartItemCodes = [];

    public function mount(): void
    {
        $rawItemCodes = request()->query('item_codes', '');

        $this->selectedCartItemCodes = collect(explode(',', (string) $rawItemCodes))
            ->map(fn (string $itemCode): string => trim($itemCode))
            ->filter(fn (string $itemCode): bool => $itemCode !== '')
            ->unique()
            ->values()
            ->all();

        abort_if($this->selectedCartItemCodes === [], 422, 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.');
    }

    public function render(): View
    {
        $user = $this->resolveUser();

        $cart = Cart::query()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $items = $cart->items()
            ->whereIn('cart_item_code', $this->selectedCartItemCodes)
            ->with([
                'listing.variant.product',
                'listing.variant.region',
                'listing.variant.platform',
                'listing.variant.operatingSystem',
            ])
            ->orderBy('id')
            ->get();

        abort_if($items->count() !== count($this->selectedCartItemCodes), 422, 'Một hoặc nhiều sản phẩm đã chọn không còn khả dụng.');

        $subtotal = $items->sum(function (CartItem $item): float {
            return (float) ($item->listing?->price ?? 0) * (int) $item->quantity;
        });

        return view('pages.shop.checkout-review', [
            'cart'          => $cart,
            'items'         => $items,
            'subtotal'      => $subtotal,
            'selectedCount' => $items->count(),
        ])->layout('components.layouts.shop');
    }

    protected function resolveUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }
}
