<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Seller;

use App\Enums\ComplaintStatus;
use App\Enums\GeneralStatus;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Seller Dashboard')]
class Dashboard extends Component
{
    public function render(): View
    {
        $user = $this->resolveUser();
        $user->loadMissing('seller.wallet');

        $seller = $this->resolveSeller($user);

        return view('pages.shop.seller.dashboard', [
            'user'           => $user,
            'seller'         => $seller,
            'metrics'        => $this->metrics($seller),
            'recentProducts' => $this->recentProducts($seller),
            'recentListings' => $this->recentListings($seller),
            'portalStatus'   => $this->portalStatus($seller),
        ])->layout('components.layouts.seller', [
            'title'         => 'Seller Dashboard',
            'user'          => $user,
            'seller'        => $seller,
            'activeSection' => 'dashboard',
        ]);
    }

    protected function metrics(Seller $seller): array
    {
        $productsQuery = Product::query()->where('submitted_by_seller_id', $seller->id);
        $listingsQuery = ProductListing::query()->where('seller_id', $seller->id);
        $complaints = $this->complaints($seller);
        $financialSummary = $this->financialSummary($seller);

        return [
            'products'         => (clone $productsQuery)->count(),
            'activeProducts'   => (clone $productsQuery)->where('status', GeneralStatus::Active)->count(),
            'listings'         => (clone $listingsQuery)->count(),
            'activeListings'   => (clone $listingsQuery)->where('status', ProductListingStatus::Active)->count(),
            'complaints'       => $complaints->count(),
            'activeComplaints' => $complaints->whereIn('status', [ComplaintStatus::Open, ComplaintStatus::InProcess, ComplaintStatus::Escalated])->count(),
            'walletBalance'    => (float) ($seller->wallet?->balance ?? 0),
            'walletHolding'    => (float) ($seller->wallet?->holding ?? 0),
            'sellerEarnings'   => $financialSummary['seller_earnings'],
            'platformFee'      => $financialSummary['platform_fee'],
            'availableKeys'    => $this->availableKeys($seller),
        ];
    }

    /**
     * @return array{seller_earnings: float, platform_fee: float}
     */
    protected function financialSummary(Seller $seller): array
    {
        $summary = OrderItem::query()
            ->where('seller_id', $seller->id)
            ->whereIn('status', [
                OrderStatus::Delivered->value,
                OrderStatus::Disputing->value,
                OrderStatus::Completed->value,
            ])
            ->selectRaw('COALESCE(SUM(seller_amount), 0) as seller_earnings, COALESCE(SUM(platform_fee), 0) as platform_fee')
            ->first();

        return [
            'seller_earnings' => (float) ($summary?->seller_earnings ?? 0),
            'platform_fee'    => (float) ($summary?->platform_fee ?? 0),
        ];
    }

    /**
     * @return Collection<int, Complaint>
     */
    protected function complaints(Seller $seller): Collection
    {
        return Complaint::query()
            ->whereHas('orderItem', function (Builder $query) use ($seller): void {
                $query->where('seller_id', $seller->id);
            })
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    protected function recentProducts(Seller $seller): Collection
    {
        return Product::query()
            ->where('submitted_by_seller_id', $seller->id)
            ->withCount(['variants', 'listings'])
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();
    }

    /**
     * @return Collection<int, ProductListing>
     */
    protected function recentListings(Seller $seller): Collection
    {
        return ProductListing::query()
            ->where('seller_id', $seller->id)
            ->with([
                'variant.product',
                'variant.region',
                'variant.platform',
                'variant.operatingSystem',
            ])
            ->withCount([
                'keys as available_keys_count' => function (Builder $query): void {
                    $query->where('status', ProductKeyStatus::Available->value);
                },
            ])
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();
    }

    protected function availableKeys(Seller $seller): int
    {
        return ProductKey::query()
            ->where('status', ProductKeyStatus::Available->value)
            ->whereHas('listing', function (Builder $query) use ($seller): void {
                $query->where('seller_id', $seller->id);
            })
            ->count();
    }

    protected function portalStatus(Seller $seller): array
    {
        return [
            'headline'    => 'Seller portal ready',
            'description' => 'Track catalog health, wallet balances, and active inventory from one place.',
            'badge'       => $seller->kyc_status->label(),
            'badgeClass'  => match ($seller->kyc_status) {
                KycStatus::Approved => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                KycStatus::Pending  => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                KycStatus::Rejected => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                default             => 'bg-sky-500/10 text-sky-700 dark:text-sky-300',
            },
        ];
    }

    protected function resolveUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    protected function resolveSeller(User $user): Seller
    {
        $seller = $user->seller;

        abort_unless($seller instanceof Seller, 403);
        abort_unless($user->role === UserRole::Seller && $seller->kyc_status === KycStatus::Approved, 403);

        return $seller;
    }
}
