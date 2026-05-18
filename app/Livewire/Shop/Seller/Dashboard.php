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
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Bảng điều khiển người bán')]
class Dashboard extends Component
{
    /**
     * @var array{labels: list<string>, revenue: list<float>, orders: list<int>}
     */
    public array $revenueOrdersChart = ['labels' => [], 'revenue' => [], 'orders' => []];

    /**
     * @var array{labels: list<string>, values: list<int>}
     */
    public array $topProductsChart = ['labels' => [], 'values' => []];

    public function render(): View
    {
        $user = $this->resolveUser();
        $user->loadMissing('seller.wallet');

        $seller = $this->resolveSeller($user);
        [$startDate, $endDate] = $this->dashboardRange();

        $this->revenueOrdersChart = $this->revenueOrdersChart($seller, $startDate, $endDate);
        $topProducts = $this->topProducts($seller, $startDate, $endDate);
        $this->topProductsChart = [
            'labels' => $topProducts->pluck('label')->all(),
            'values' => $topProducts->pluck('quantity_sold')->all(),
        ];

        return view('pages.shop.seller.dashboard', [
            'user'               => $user,
            'seller'             => $seller,
            'rangeLabel'         => $this->rangeLabel($startDate, $endDate),
            'metrics'            => $this->metrics($seller, $startDate, $endDate),
            'recentProducts'     => $this->recentProducts($seller),
            'recentListings'     => $this->recentListings($seller),
            'topProducts'        => $topProducts,
            'portalStatus'       => $this->portalStatus($seller),
            'revenueOrdersChart' => $this->revenueOrdersChart,
            'topProductsChart'   => $this->topProductsChart,
        ])->layout('components.layouts.seller', [
            'title'         => 'Bảng điều khiển người bán',
            'user'          => $user,
            'seller'        => $seller,
            'activeSection' => 'dashboard',
        ]);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function dashboardRange(): array
    {
        $endDate = CarbonImmutable::now()->endOfDay();
        $startDate = $endDate->subDays(29)->startOfDay();

        return [$startDate, $endDate];
    }

    protected function metrics(Seller $seller, CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $productsQuery = Product::query()->where('submitted_by_seller_id', $seller->id);
        $listingsQuery = ProductListing::query()->where('seller_id', $seller->id);
        $salesQuery = $this->salesItemsQuery($seller, $startDate, $endDate);
        $complaints = $this->complaints($seller, $startDate, $endDate);
        $orderCount = (clone $salesQuery)->distinct()->count('order_id');
        $complaintCount = $complaints->count();
        $resolvedComplaintCount = (clone $complaints)
            ->whereIn('status', [ComplaintStatus::ApprovedRefund, ComplaintStatus::RejectedRelease])
            ->count();

        return [
            'rangeLabel'          => $this->rangeLabel($startDate, $endDate),
            'products'            => (clone $productsQuery)->count(),
            'activeProducts'      => (clone $productsQuery)->where('status', GeneralStatus::Active)->count(),
            'listings'            => (clone $listingsQuery)->count(),
            'activeListings'      => (clone $listingsQuery)->where('status', ProductListingStatus::Active)->count(),
            'orders'              => $orderCount,
            'itemsSold'           => (int) (clone $salesQuery)->sum('quantity'),
            'complaints'          => $complaintCount,
            'openComplaints'      => (clone $complaints)->where('status', ComplaintStatus::Open)->count(),
            'inProcessComplaints' => (clone $complaints)->where('status', ComplaintStatus::InProcess)->count(),
            'escalatedComplaints' => (clone $complaints)->where('status', ComplaintStatus::Escalated)->count(),
            'resolvedComplaints'  => $resolvedComplaintCount,
            'walletBalance'       => (float) ($seller->wallet?->balance ?? 0),
            'walletHolding'       => (float) ($seller->wallet?->holding ?? 0),
            'sellerEarnings'      => (float) (clone $salesQuery)->sum('seller_amount'),
            'platformFee'         => (float) (clone $salesQuery)->sum('platform_fee'),
            'availableKeys'       => $this->availableKeys($seller),
            'complaintRate'       => $orderCount > 0
                ? round(($complaintCount / $orderCount) * 100, 2)
                : 0.0,
        ];
    }

    /**
     * @return Builder<OrderItem>
     */
    protected function salesItemsQuery(Seller $seller, ?CarbonImmutable $startDate = null, ?CarbonImmutable $endDate = null): Builder
    {
        $query = OrderItem::query()
            ->where('order_items.seller_id', $seller->id)
            ->whereIn('order_items.status', $this->recognizedRevenueStatuses());

        if ($startDate !== null && $endDate !== null) {
            $query->whereBetween('order_items.created_at', [$startDate, $endDate]);
        }

        return $query;
    }

    /**
     * @return Collection<int, Complaint>
     */
    protected function complaints(Seller $seller, ?CarbonImmutable $startDate = null, ?CarbonImmutable $endDate = null): Collection
    {
        $query = Complaint::query()
            ->whereHas('orderItem', function (Builder $query) use ($seller): void {
                $query->where('seller_id', $seller->id);
            })
            ->with(['orderItem.order.buyer']);

        if ($startDate !== null && $endDate !== null) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, array{label: string, quantity_sold: int, orders_count: int, revenue: float}>
     */
    protected function topProducts(Seller $seller, CarbonImmutable $startDate, CarbonImmutable $endDate): Collection
    {
        return $this->salesItemsQuery($seller, $startDate, $endDate)
            ->join('product_listings', 'product_listings.id', '=', 'order_items.listing_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('COALESCE(product_listings.display_name, order_items.product_name_snapshot, products.name) as label')
            ->selectRaw('SUM(order_items.quantity) as quantity_sold')
            ->selectRaw('COUNT(DISTINCT order_items.order_id) as orders_count')
            ->selectRaw('COALESCE(SUM(order_items.seller_amount), 0) as revenue')
            ->groupBy('product_listings.id', 'product_listings.display_name', 'order_items.product_name_snapshot', 'products.name')
            ->orderByDesc('quantity_sold')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(function (object $product): array {
                return [
                    'label'         => (string) $product->label,
                    'quantity_sold' => (int) $product->quantity_sold,
                    'orders_count'  => (int) $product->orders_count,
                    'revenue'       => (float) $product->revenue,
                ];
            });
    }

    /**
     * @return array{labels: list<string>, revenue: list<float>, orders: list<int>}
     */
    protected function revenueOrdersChart(Seller $seller, CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $revenueByDate = $this->salesItemsQuery($seller, $startDate, $endDate)
            ->selectRaw('DATE(order_items.created_at) as period')
            ->selectRaw('COALESCE(SUM(order_items.seller_amount), 0) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $ordersByDate = $this->salesItemsQuery($seller, $startDate, $endDate)
            ->selectRaw('DATE(order_items.created_at) as period')
            ->selectRaw('COUNT(DISTINCT order_items.order_id) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $labels = [];
        $revenue = [];
        $orders = [];

        foreach (CarbonPeriod::create($startDate->toDateString(), $endDate->toDateString()) as $date) {
            $period = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $revenue[] = (float) ($revenueByDate[$period] ?? 0);
            $orders[] = (int) ($ordersByDate[$period] ?? 0);
        }

        return [
            'labels'  => $labels,
            'revenue' => $revenue,
            'orders'  => $orders,
        ];
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

    protected function rangeLabel(CarbonImmutable $startDate, CarbonImmutable $endDate): string
    {
        return $startDate->isSameDay($endDate)
            ? $startDate->format('d/m/Y')
            : $startDate->format('d/m/Y').' - '.$endDate->format('d/m/Y');
    }

    /**
     * @return list<int>
     */
    protected function recognizedRevenueStatuses(): array
    {
        return [
            OrderStatus::Delivered->value,
            OrderStatus::Disputing->value,
            OrderStatus::Completed->value,
        ];
    }

    protected function portalStatus(Seller $seller): array
    {
        return [
            'headline'    => 'Cổng người bán đã sẵn sàng',
            'description' => 'Theo dõi tình trạng danh mục, số dư ví và tồn kho đang bán tại một nơi.',
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
