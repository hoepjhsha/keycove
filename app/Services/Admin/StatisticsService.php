<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\ComplaintStatus;
use App\Enums\EscrowStatus;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\WalletType;
use App\Enums\WithdrawStatus;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdraw;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StatisticsService
{
    /**
     * @return array{
     *     revenue: array{gmv: float, platformRevenue: float, platformFeeRevenue: float, platformOwnedRevenue: float, platformTakeRate: float},
     *     orders: array{ordersCount: int, revenueItemsCount: int, averageOrderValue: float, revenuePerOrder: float},
     *     complaints: array{shopAdminComplaintCount: int, sellerComplaintCount: int, shopAdminComplaintRate: float, sellerComplaintRate: float},
     *     categories: array{favoriteCategories: list<array{category_id: int, category_name: string, units_sold: int, revenue: float}>},
     *     products: array{topProducts: list<array{product_id: int, product_name: string, units_sold: int, revenue: float, platform_fee_revenue: float, seller_net_revenue: float}>, topSellerProducts: list<array{seller_name: string, product_name: string, units_sold: int, revenue: float, platform_fee_revenue: float}>},
     *     sellers: array{topSellers: list<array{seller_id: int, seller_name: string, orders_count: int, units_sold: int, gross_revenue: float, platform_fee_revenue: float, seller_net_revenue: float, complaint_count: int, complaint_rate: float}>, worstComplaintRates: list<array{seller_id: int, seller_name: string, orders_count: int, units_sold: int, gross_revenue: float, complaint_count: int, complaint_rate: float}>},
     *     charts: array<string, array{labels: list<string>, values?: list<float|int>, revenue?: list<float>, platformFeeRevenue?: list<float>, platformOwnedRevenue?: list<float>, orders?: list<int>, quantities?: list<int>, grossRevenue?: list<float>}>,
     *     highlights: array{topCategory: ?array{category_id: int, category_name: string, units_sold: int, revenue: float}, topProduct: ?array{product_id: int, product_name: string, units_sold: int, revenue: float, platform_fee_revenue: float, seller_net_revenue: float}, topSeller: ?array{seller_id: int, seller_name: string, orders_count: int, units_sold: int, gross_revenue: float, platform_fee_revenue: float, seller_net_revenue: float, complaint_count: int, complaint_rate: float}},
     *     ai_context: array<string, mixed>
     * }
     */
    public function buildSnapshot(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $revenue = $this->revenueSummary($startDate, $endDate);
        $orders = $this->orderSummary($startDate, $endDate, $revenue);
        $categories = $this->categoryPreferences($startDate, $endDate);
        $products = $this->productPerformance($startDate, $endDate);
        $sellers = $this->sellerPerformance($startDate, $endDate);
        $complaints = $this->complaintSummary($startDate, $endDate);
        $charts = [
            'revenue_orders'       => $this->dashboardRevenueOrdersChart($startDate, $endDate),
            'category_preferences' => $this->categoryPreferencesChart($categories['favoriteCategories']),
            'seller_revenue'       => $this->sellerRevenueChart($sellers['topSellers']),
        ];

        return [
            'revenue'    => $revenue,
            'orders'     => $orders,
            'complaints' => $complaints,
            'categories' => $categories,
            'products'   => $products,
            'sellers'    => $sellers,
            'charts'     => $charts,
            'highlights' => [
                'topCategory' => $categories['favoriteCategories'][0] ?? null,
                'topProduct'  => $products['topProducts'][0] ?? null,
                'topSeller'   => $sellers['topSellers'][0] ?? null,
            ],
            'ai_context' => $this->summarizeForAi([
                'revenue'    => $revenue,
                'orders'     => $orders,
                'complaints' => $complaints,
                'categories' => $categories,
                'products'   => $products,
                'sellers'    => $sellers,
                'charts'     => $charts,
                'highlights' => [
                    'topCategory' => $categories['favoriteCategories'][0] ?? null,
                    'topProduct'  => $products['topProducts'][0] ?? null,
                    'topSeller'   => $sellers['topSellers'][0] ?? null,
                ],
            ]),
        ];
    }

    /**
     * @param  array{
     *     revenue: array<string, mixed>,
     *     orders: array<string, mixed>,
     *     complaints: array<string, mixed>,
     *     categories: array<string, mixed>,
     *     products: array<string, mixed>,
     *     sellers: array<string, mixed>,
     *     highlights: array<string, mixed>,
     *     charts: array<string, mixed>
     * }  $snapshot
     * @return array<string, mixed>
     */
    public function summarizeForAi(array $snapshot): array
    {
        return [
            'revenue'    => $snapshot['revenue'],
            'orders'     => $snapshot['orders'],
            'complaints' => $snapshot['complaints'],
            'categories' => $snapshot['categories'],
            'products'   => $snapshot['products'],
            'sellers'    => $snapshot['sellers'],
            'highlights' => $snapshot['highlights'],
            'charts'     => $snapshot['charts'],
        ];
    }

    /**
     * @return array{gmv: float, platformRevenue: float, platformFeeRevenue: float, platformOwnedRevenue: float, platformTakeRate: float}
     */
    protected function revenueSummary(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $revenueItems = $this->revenueItemsQuery($startDate, $endDate);

        $gmv = (float) (clone $revenueItems)->sum('order_items.subtotal');
        $platformFeeRevenue = (float) (clone $revenueItems)
            ->whereNotNull('order_items.seller_id')
            ->sum('order_items.platform_fee');
        $platformOwnedRevenue = (float) (clone $revenueItems)
            ->whereNull('order_items.seller_id')
            ->sum('order_items.seller_amount');
        $platformRevenue = round($platformFeeRevenue + $platformOwnedRevenue, 2);

        return [
            'gmv'                  => round($gmv, 2),
            'platformRevenue'      => $platformRevenue,
            'platformFeeRevenue'   => round($platformFeeRevenue, 2),
            'platformOwnedRevenue' => round($platformOwnedRevenue, 2),
            'platformTakeRate'     => $gmv > 0 ? round(($platformRevenue / $gmv) * 100, 2) : 0.0,
        ];
    }

    /**
     * @param  array{gmv: float, platformRevenue: float, platformFeeRevenue: float, platformOwnedRevenue: float, platformTakeRate: float}  $revenue
     * @return array{ordersCount: int, revenueItemsCount: int, averageOrderValue: float, revenuePerOrder: float}
     */
    protected function orderSummary(CarbonImmutable $startDate, CarbonImmutable $endDate, array $revenue): array
    {
        $revenueItems = $this->revenueItemsQuery($startDate, $endDate);

        $ordersCount = (int) Order::query()
            ->where('payment_status', PaymentStatus::Completed->value)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $revenueItemsCount = (int) (clone $revenueItems)->count();

        return [
            'ordersCount'       => $ordersCount,
            'revenueItemsCount' => $revenueItemsCount,
            'averageOrderValue' => $ordersCount > 0 ? round($revenue['gmv'] / $ordersCount, 2) : 0.0,
            'revenuePerOrder'   => $ordersCount > 0 ? round($revenue['platformRevenue'] / $ordersCount, 2) : 0.0,
        ];
    }

    /**
     * @return array{shopAdminComplaintCount: int, sellerComplaintCount: int, shopAdminComplaintRate: float, sellerComplaintRate: float, shopAdminRevenueItems: int, sellerRevenueItems: int, worstSellerComplaintRates: list<array{seller_id: int, seller_name: string, orders_count: int, units_sold: int, gross_revenue: float, complaint_count: int, complaint_rate: float}>}
     */
    protected function complaintSummary(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $revenueScopeCounts = $this->revenueScopeCounts($startDate, $endDate);
        $complaintScopeCounts = $this->complaintScopeCounts($startDate, $endDate);

        return [
            'shopAdminComplaintCount'   => $complaintScopeCounts['platform'],
            'sellerComplaintCount'      => $complaintScopeCounts['seller'],
            'shopAdminComplaintRate'    => $revenueScopeCounts['platform'] > 0 ? round(($complaintScopeCounts['platform'] / $revenueScopeCounts['platform']) * 100, 2) : 0.0,
            'sellerComplaintRate'       => $revenueScopeCounts['seller'] > 0 ? round(($complaintScopeCounts['seller'] / $revenueScopeCounts['seller']) * 100, 2) : 0.0,
            'shopAdminRevenueItems'     => $revenueScopeCounts['platform'],
            'sellerRevenueItems'        => $revenueScopeCounts['seller'],
            'worstSellerComplaintRates' => $this->sellerComplaintRates($startDate, $endDate),
        ];
    }

    /**
     * @return array{favoriteCategories: list<array{category_id: int, category_name: string, units_sold: int, revenue: float}>}
     */
    protected function categoryPreferences(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $rows = $this->revenueItemsQuery($startDate, $endDate)
            ->join('product_listings', 'product_listings.id', '=', 'order_items.listing_id')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->join('category_product', 'category_product.product_id', '=', 'products.id')
            ->join('categories', 'categories.id', '=', 'category_product.category_id')
            ->selectRaw('categories.id as category_id, categories.name as category_name')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as units_sold')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('units_sold')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get();

        return [
            'favoriteCategories' => $rows->map(fn (object $row): array => [
                'category_id'   => (int) $row->category_id,
                'category_name' => (string) $row->category_name,
                'units_sold'    => (int) $row->units_sold,
                'revenue'       => (float) $row->revenue,
            ])->all(),
        ];
    }

    /**
     * @return array{topProducts: list<array{product_id: int, product_name: string, units_sold: int, revenue: float, platform_fee_revenue: float, seller_net_revenue: float}>, topSellerProducts: list<array{seller_name: string, product_name: string, units_sold: int, revenue: float, platform_fee_revenue: float}>}
     */
    protected function productPerformance(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $topProducts = $this->revenueItemsQuery($startDate, $endDate)
            ->join('product_listings', 'product_listings.id', '=', 'order_items.listing_id')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('products.id as product_id, products.name as product_name')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as units_sold')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as revenue')
            ->selectRaw('COALESCE(SUM(order_items.platform_fee), 0) as platform_fee_revenue')
            ->selectRaw('COALESCE(SUM(order_items.seller_amount), 0) as seller_net_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('units_sold')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get();

        $topSellerProducts = $this->revenueItemsQuery($startDate, $endDate)
            ->whereNotNull('order_items.seller_id')
            ->join('sellers', 'sellers.id', '=', 'order_items.seller_id')
            ->join('product_listings', 'product_listings.id', '=', 'order_items.listing_id')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('sellers.shop_name as seller_name, products.name as product_name')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as units_sold')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as revenue')
            ->selectRaw('COALESCE(SUM(order_items.platform_fee), 0) as platform_fee_revenue')
            ->groupBy('sellers.shop_name', 'products.name')
            ->orderByDesc('units_sold')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get();

        return [
            'topProducts' => $topProducts->map(fn (object $row): array => [
                'product_id'           => (int) $row->product_id,
                'product_name'         => (string) $row->product_name,
                'units_sold'           => (int) $row->units_sold,
                'revenue'              => (float) $row->revenue,
                'platform_fee_revenue' => (float) $row->platform_fee_revenue,
                'seller_net_revenue'   => (float) $row->seller_net_revenue,
            ])->all(),
            'topSellerProducts' => $topSellerProducts->map(fn (object $row): array => [
                'seller_name'          => (string) $row->seller_name,
                'product_name'         => (string) $row->product_name,
                'units_sold'           => (int) $row->units_sold,
                'revenue'              => (float) $row->revenue,
                'platform_fee_revenue' => (float) $row->platform_fee_revenue,
            ])->all(),
        ];
    }

    /**
     * @return array{topSellers: list<array{seller_id: int, seller_name: string, orders_count: int, units_sold: int, gross_revenue: float, platform_fee_revenue: float, seller_net_revenue: float, complaint_count: int, complaint_rate: float}>, worstComplaintRates: list<array{seller_id: int, seller_name: string, orders_count: int, units_sold: int, gross_revenue: float, complaint_count: int, complaint_rate: float}>}
     */
    protected function sellerPerformance(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $rows = $this->sellerPerformanceQuery($startDate, $endDate)
            ->get();

        $complaintCounts = $this->sellerComplaintCounts($startDate, $endDate);

        $mapped = $rows->map(function (object $row) use ($complaintCounts): array {
            $revenueItemsCount = (int) $row->revenue_items_count;
            $complaintCount = (int) ($complaintCounts[(int) $row->seller_id] ?? 0);

            return [
                'seller_id'            => (int) $row->seller_id,
                'seller_name'          => (string) $row->seller_name,
                'orders_count'         => (int) $row->orders_count,
                'units_sold'           => (int) $row->units_sold,
                'gross_revenue'        => (float) $row->gross_revenue,
                'platform_fee_revenue' => (float) $row->platform_fee_revenue,
                'seller_net_revenue'   => (float) $row->seller_net_revenue,
                'complaint_count'      => $complaintCount,
                'complaint_rate'       => $revenueItemsCount > 0 ? round(($complaintCount / $revenueItemsCount) * 100, 2) : 0.0,
                'revenue_items_count'  => $revenueItemsCount,
            ];
        });

        $topSellers = $mapped
            ->sort(function (array $left, array $right): int {
                return [$right['gross_revenue'], $right['units_sold']] <=> [$left['gross_revenue'], $left['units_sold']];
            })
            ->take(5)
            ->values()
            ->all();

        $worstComplaintRates = $mapped
            ->sort(function (array $left, array $right): int {
                return [$right['complaint_rate'], $right['complaint_count']] <=> [$left['complaint_rate'], $left['complaint_count']];
            })
            ->take(5)
            ->values()
            ->map(fn (array $row): array => [
                'seller_id'       => $row['seller_id'],
                'seller_name'     => $row['seller_name'],
                'orders_count'    => $row['orders_count'],
                'units_sold'      => $row['units_sold'],
                'gross_revenue'   => $row['gross_revenue'],
                'complaint_count' => $row['complaint_count'],
                'complaint_rate'  => $row['complaint_rate'],
            ])->all();

        return [
            'topSellers'          => $topSellers,
            'worstComplaintRates' => $worstComplaintRates,
        ];
    }

    /**
     * @return array{labels: list<string>, revenue: list<float>, platformFeeRevenue: list<float>, platformOwnedRevenue: list<float>, orders: list<int>}
     */
    protected function dashboardRevenueOrdersChart(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $rows = $this->revenueItemsQuery($startDate, $endDate)
            ->selectRaw('DATE(orders.created_at) as period')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as revenue')
            ->selectRaw('COALESCE(SUM(CASE WHEN order_items.seller_id IS NULL THEN order_items.seller_amount ELSE 0 END), 0) as platform_owned_revenue')
            ->selectRaw('COALESCE(SUM(CASE WHEN order_items.seller_id IS NOT NULL THEN order_items.platform_fee ELSE 0 END), 0) as platform_fee_revenue')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $byPeriod = $rows->keyBy('period');
        $labels = [];
        $platformFeeRevenue = [];
        $platformOwnedRevenue = [];
        $revenueSeries = [];
        $orderSeries = [];

        foreach (CarbonPeriod::create($startDate->toDateString(), $endDate->toDateString()) as $date) {
            $period = $date->format('Y-m-d');
            $row = $byPeriod->get($period);

            $labels[] = $date->format('d/m');
            $revenueSeries[] = (float) ($row->revenue ?? 0);
            $platformFeeRevenue[] = (float) ($row->platform_fee_revenue ?? 0);
            $platformOwnedRevenue[] = (float) ($row->platform_owned_revenue ?? 0);
            $orderSeries[] = (int) ($row->orders_count ?? 0);
        }

        return [
            'labels'               => $labels,
            'revenue'              => $revenueSeries,
            'platformFeeRevenue'   => $platformFeeRevenue,
            'platformOwnedRevenue' => $platformOwnedRevenue,
            'orders'               => $orderSeries,
        ];
    }

    /**
     * @param  list<array{category_id: int, category_name: string, units_sold: int, revenue: float}>  $favoriteCategories
     * @return array{labels: list<string>, quantities: list<int>, values: list<float>}
     */
    protected function categoryPreferencesChart(array $favoriteCategories): array
    {
        return [
            'labels'     => array_values(array_map(fn (array $row): string => $row['category_name'], $favoriteCategories)),
            'quantities' => array_values(array_map(fn (array $row): int => $row['units_sold'], $favoriteCategories)),
            'values'     => array_values(array_map(fn (array $row): float => $row['revenue'], $favoriteCategories)),
        ];
    }

    /**
     * @param  list<array{seller_id: int, seller_name: string, orders_count: int, units_sold: int, gross_revenue: float, platform_fee_revenue: float, seller_net_revenue: float, complaint_count: int, complaint_rate: float}>  $topSellers
     * @return array{labels: list<string>, grossRevenue: list<float>, values: list<float>}
     */
    protected function sellerRevenueChart(array $topSellers): array
    {
        return [
            'labels'       => array_values(array_map(fn (array $row): string => $row['seller_name'], $topSellers)),
            'grossRevenue' => array_values(array_map(fn (array $row): float => $row['gross_revenue'], $topSellers)),
            'values'       => array_values(array_map(fn (array $row): float => $row['gross_revenue'], $topSellers)),
        ];
    }

    /**
     * @return array{platform: int, seller: int}
     */
    protected function revenueScopeCounts(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $row = $this->revenueItemsQuery($startDate, $endDate)
            ->selectRaw('SUM(CASE WHEN order_items.seller_id IS NULL THEN 1 ELSE 0 END) as platform_count')
            ->selectRaw('SUM(CASE WHEN order_items.seller_id IS NOT NULL THEN 1 ELSE 0 END) as seller_count')
            ->first();

        return [
            'platform' => (int) ($row?->platform_count ?? 0),
            'seller'   => (int) ($row?->seller_count ?? 0),
        ];
    }

    /**
     * @return array{platform: int, seller: int}
     */
    protected function complaintScopeCounts(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $row = Complaint::query()
            ->join('order_items', 'complaints.order_item_id', '=', 'order_items.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('complaints.created_at', [$startDate, $endDate])
            ->whereIn('order_items.status', $this->recognizedRevenueStatuses())
            ->selectRaw('SUM(CASE WHEN order_items.seller_id IS NULL THEN 1 ELSE 0 END) as platform_count')
            ->selectRaw('SUM(CASE WHEN order_items.seller_id IS NOT NULL THEN 1 ELSE 0 END) as seller_count')
            ->first();

        return [
            'platform' => (int) ($row?->platform_count ?? 0),
            'seller'   => (int) ($row?->seller_count ?? 0),
        ];
    }

    /**
     * @return list<array{seller_id: int, seller_name: string, orders_count: int, units_sold: int, gross_revenue: float, complaint_count: int, complaint_rate: float}>
     */
    protected function sellerComplaintRates(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        return $this->sellerPerformance($startDate, $endDate)['worstComplaintRates'];
    }

    /**
     * @return Builder<OrderItem>
     */
    protected function revenueItemsQuery(CarbonImmutable $startDate, CarbonImmutable $endDate): Builder
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('order_items.status', $this->recognizedRevenueStatuses());
    }

    /**
     * @return Builder<OrderItem>
     */
    protected function sellerPerformanceQuery(CarbonImmutable $startDate, CarbonImmutable $endDate): Builder
    {
        return $this->revenueItemsQuery($startDate, $endDate)
            ->whereNotNull('order_items.seller_id')
            ->join('sellers', 'sellers.id', '=', 'order_items.seller_id')
            ->selectRaw('sellers.id as seller_id, sellers.shop_name as seller_name')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->selectRaw('COUNT(DISTINCT order_items.id) as revenue_items_count')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as units_sold')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as gross_revenue')
            ->selectRaw('COALESCE(SUM(order_items.platform_fee), 0) as platform_fee_revenue')
            ->selectRaw('COALESCE(SUM(order_items.seller_amount), 0) as seller_net_revenue')
            ->groupBy('sellers.id', 'sellers.shop_name');
    }

    /**
     * @return array<int, int>
     */
    protected function sellerComplaintCounts(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        return Complaint::query()
            ->join('order_items', 'complaints.order_item_id', '=', 'order_items.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('complaints.created_at', [$startDate, $endDate])
            ->whereIn('order_items.status', $this->recognizedRevenueStatuses())
            ->whereNotNull('order_items.seller_id')
            ->selectRaw('order_items.seller_id as seller_id')
            ->selectRaw('COUNT(DISTINCT complaints.id) as complaint_count')
            ->groupBy('order_items.seller_id')
            ->pluck('complaint_count', 'seller_id')
            ->map(fn (mixed $value): int => (int) $value)
            ->all();
    }

    /**
     * @return array<string, int|float>
     */
    protected function quickStats(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        return [
            'gmv' => (float) Order::query()
                ->where('payment_status', PaymentStatus::Completed->value)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_price'),
            'netRevenue' => (float) OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->whereIn('order_items.status', $this->recognizedRevenueStatuses())
                ->sum('order_items.platform_fee'),
            'openComplaints'   => Complaint::query()->whereIn('status', $this->activeComplaintStatuses())->count(),
            'pendingKycSeller' => Seller::query()->where('kyc_status', KycStatus::Pending->value)->count(),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    protected function finance(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $internalWallet = Wallet::query()->where('type', WalletType::Internal->value)->first();

        return [
            'escrowHolding'         => (float) Escrow::query()->where('status', EscrowStatus::Holding->value)->sum('amount'),
            'internalWalletBalance' => (float) ($internalWallet?->balance ?? 0),
            'completedWithdrawals'  => (float) Withdraw::query()
                ->where('status', WithdrawStatus::Completed->value)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount'),
            'completedWithdrawalCount' => Withdraw::query()
                ->where('status', WithdrawStatus::Completed->value)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'pendingWithdrawals' => (float) Withdraw::query()
                ->where('status', WithdrawStatus::Pending->value)
                ->sum('amount'),
            'pendingWithdrawalCount' => Withdraw::query()->where('status', WithdrawStatus::Pending->value)->count(),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    protected function operations(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $orderCount = Order::query()->whereBetween('created_at', [$startDate, $endDate])->count();
        $complaintsCount = Complaint::query()->whereBetween('created_at', [$startDate, $endDate])->count();

        $resolvedComplaints = Complaint::query()
            ->whereNotNull('resolved_at')
            ->whereBetween('resolved_at', [$startDate, $endDate])
            ->get(['created_at', 'resolved_at']);

        $averageResolutionMinutes = (float) $resolvedComplaints
            ->filter(fn (Complaint $complaint): bool => $complaint->resolved_at !== null)
            ->avg(fn (Complaint $complaint) => $complaint->created_at->diffInMinutes($complaint->resolved_at));

        return [
            'disputeRate'          => $orderCount > 0 ? round(($complaintsCount / $orderCount) * 100, 2) : 0.0,
            'pendingKyc'           => Seller::query()->where('kyc_status', KycStatus::Pending->value)->count(),
            'avgResolutionHours'   => round($averageResolutionMinutes / 60, 2),
            'pendingListings'      => ProductListing::query()->where('status', ProductListingStatus::Pending->value)->count(),
            'activeComplaintCount' => Complaint::query()->whereIn('status', $this->activeComplaintStatuses())->count(),
        ];
    }

    /**
     * @return array<string, int|float|array<int, array{name: string, revenue: float}>>
     */
    protected function market(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        return [
            'availableKeys'  => ProductKey::query()->where('status', ProductKeyStatus::Available->value)->count(),
            'activeListings' => ProductListing::query()->where('status', ProductListingStatus::Active->value)->count(),
            'refundedKeys'   => ProductKey::query()->where('status', ProductKeyStatus::Refunded->value)->count(),
            'topRegions'     => $this->topRegions($startDate, $endDate),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    protected function growth(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        return [
            'newUsers'        => User::query()->whereBetween('created_at', [$startDate, $endDate])->count(),
            'newSellers'      => Seller::query()->whereBetween('created_at', [$startDate, $endDate])->count(),
            'avgReviewRating' => round((float) (Review::query()->avg('rating') ?? 0), 2),
        ];
    }

    /**
     * @return array{labels: list<string>, revenue: list<float>, orders: list<int>}
     */
    protected function revenueOrdersChart(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $gmvByDate = Order::query()
            ->where('payment_status', PaymentStatus::Completed->value)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as period')
            ->selectRaw('COALESCE(SUM(total_price), 0) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $ordersByDate = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as period')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $labels = [];
        $revenue = [];
        $orders = [];

        foreach (CarbonPeriod::create($startDate->toDateString(), $endDate->toDateString()) as $date) {
            $period = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $revenue[] = (float) ($gmvByDate[$period] ?? 0);
            $orders[] = (int) ($ordersByDate[$period] ?? 0);
        }

        return [
            'labels'  => $labels,
            'revenue' => $revenue,
            'orders'  => $orders,
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    protected function platformRevenueChart(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_listings', 'product_listings.id', '=', 'order_items.listing_id')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('platforms', 'platforms.id', '=', 'product_variants.platform_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('order_items.status', $this->recognizedRevenueStatuses())
            ->selectRaw('platforms.name as label, COALESCE(SUM(order_items.subtotal), 0) as revenue')
            ->groupBy('platforms.id', 'platforms.name')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get();

        return [
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('revenue')->map(fn (mixed $value): float => (float) $value)->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, users: list<int>, sellers: list<int>}
     */
    protected function userGrowthChart(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $usersByDate = User::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as period')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $sellersByDate = Seller::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as period')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $labels = [];
        $users = [];
        $sellers = [];

        foreach (CarbonPeriod::create($startDate->toDateString(), $endDate->toDateString()) as $date) {
            $period = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $users[] = (int) ($usersByDate[$period] ?? 0);
            $sellers[] = (int) ($sellersByDate[$period] ?? 0);
        }

        return [
            'labels'  => $labels,
            'users'   => $users,
            'sellers' => $sellers,
        ];
    }

    /**
     * @return Collection<int, Order>
     */
    protected function topOrders(CarbonImmutable $startDate, CarbonImmutable $endDate): Collection
    {
        return Order::query()
            ->with('buyer')
            ->withCount('items')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('total_price')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, Complaint>
     */
    protected function urgentComplaints(): Collection
    {
        return Complaint::query()
            ->with(['orderItem.order.buyer', 'orderItem.seller'])
            ->whereIn('status', $this->activeComplaintStatuses())
            ->orderByRaw('CASE status WHEN 0 THEN 0 WHEN 1 THEN 1 WHEN 2 THEN 2 ELSE 3 END')
            ->oldest('created_at')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    protected function topSellers(CarbonImmutable $startDate, CarbonImmutable $endDate): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('sellers', 'sellers.id', '=', 'order_items.seller_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('order_items.status', $this->recognizedRevenueStatuses())
            ->selectRaw('sellers.id as seller_id, sellers.shop_name as shop_name')
            ->selectRaw('COUNT(order_items.id) as successful_orders')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as gross_revenue')
            ->selectRaw('COALESCE(SUM(order_items.platform_fee), 0) as platform_revenue')
            ->groupBy('sellers.id', 'sellers.shop_name')
            ->orderByDesc('gross_revenue')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, AuditLog>
     */
    protected function auditLogs(): Collection
    {
        return AuditLog::query()
            ->with('user')
            ->latest('created_at')
            ->limit(8)
            ->get();
    }

    /**
     * @return array<int, array{name: string, revenue: float}>
     */
    protected function topRegions(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_listings', 'product_listings.id', '=', 'order_items.listing_id')
            ->join('product_variants', 'product_variants.id', '=', 'product_listings.variant_id')
            ->join('regions', 'regions.id', '=', 'product_variants.region_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('order_items.status', $this->recognizedRevenueStatuses())
            ->selectRaw('regions.name as label, COALESCE(SUM(order_items.subtotal), 0) as revenue')
            ->groupBy('regions.id', 'regions.name')
            ->orderByDesc('revenue')
            ->limit(3)
            ->get()
            ->map(fn (object $row): array => [
                'name'    => (string) $row->label,
                'revenue' => (float) $row->revenue,
            ])->all();
    }

    /**
     * @return list<int>
     */
    protected function activeComplaintStatuses(): array
    {
        return [
            ComplaintStatus::Open->value,
            ComplaintStatus::InProcess->value,
            ComplaintStatus::Escalated->value,
        ];
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
}
