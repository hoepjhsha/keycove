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
use Illuminate\Support\Collection;

class StatisticsService
{
    /**
     * @return array{
     *     quick_stats: array<string, int|float>,
     *     finance: array<string, int|float>,
     *     operations: array<string, int|float>,
     *     market: array<string, int|float|array<int, array{name: string, revenue: float}>>,
     *     growth: array<string, int|float>,
     *     charts: array<string, array{labels: list<string>, values?: list<float|int>, revenue?: list<float>, orders?: list<int>, users?: list<int>, sellers?: list<int>}>,
     *     tables: array<string, Collection<int, mixed>>
     * }
     */
    public function buildSnapshot(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        return [
            'quick_stats' => $this->quickStats($startDate, $endDate),
            'finance'     => $this->finance($startDate, $endDate),
            'operations'  => $this->operations($startDate, $endDate),
            'market'      => $this->market($startDate, $endDate),
            'growth'      => $this->growth($startDate, $endDate),
            'charts'      => [
                'revenue_orders' => $this->revenueOrdersChart($startDate, $endDate),
                'platforms'      => $this->platformRevenueChart($startDate, $endDate),
                'user_growth'    => $this->userGrowthChart($startDate, $endDate),
            ],
            'tables' => [
                'top_orders'        => $this->topOrders($startDate, $endDate),
                'urgent_complaints' => $this->urgentComplaints(),
                'top_sellers'       => $this->topSellers($startDate, $endDate),
                'audit_logs'        => $this->auditLogs(),
            ],
        ];
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
