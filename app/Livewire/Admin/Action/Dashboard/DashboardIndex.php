<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Dashboard;

use App\Services\Admin\StatisticsService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Bảng điều khiển')]
class DashboardIndex extends Component
{
    public string $timeFilter = 'last_30_days';

    public ?string $startDate = null;

    public ?string $endDate = null;

    /** @var array{labels: list<string>, revenue: list<float>, orders: list<int>} */
    public array $revenueOrdersChart = ['labels' => [], 'revenue' => [], 'orders' => []];

    /** @var array{labels: list<string>, values: list<float>} */
    public array $platformRevenueChart = ['labels' => [], 'values' => []];

    /** @var array{labels: list<string>, users: list<int>, sellers: list<int>} */
    public array $userGrowthChart = ['labels' => [], 'users' => [], 'sellers' => []];

    protected StatisticsService $statisticsService;

    public function boot(StatisticsService $statisticsService): void
    {
        $this->statisticsService = $statisticsService;
    }

    public function mount(): void
    {
        $this->syncPresetRange();
    }

    public function updatedTimeFilter(): void
    {
        if ($this->timeFilter !== 'custom') {
            $this->syncPresetRange();
        }
    }

    public function render(): View
    {
        [$startDate, $endDate] = $this->dateRange();

        $snapshot = $this->statisticsService->buildSnapshot($startDate, $endDate);

        $this->revenueOrdersChart = $snapshot['charts']['revenue_orders'];
        $this->platformRevenueChart = $snapshot['charts']['platforms'];
        $this->userGrowthChart = $snapshot['charts']['user_growth'];

        return view('pages.admin.dashboard.index', [
            'rangeLabel'           => $this->rangeLabel($startDate, $endDate),
            'quickStats'           => $snapshot['quick_stats'],
            'finance'              => $snapshot['finance'],
            'operations'           => $snapshot['operations'],
            'market'               => $snapshot['market'],
            'growth'               => $snapshot['growth'],
            'topOrders'            => $snapshot['tables']['top_orders'],
            'urgentComplaints'     => $snapshot['tables']['urgent_complaints'],
            'topSellers'           => $snapshot['tables']['top_sellers'],
            'auditLogs'            => $snapshot['tables']['audit_logs'],
            'revenueOrdersChart'   => $this->revenueOrdersChart,
            'platformRevenueChart' => $this->platformRevenueChart,
            'userGrowthChart'      => $this->userGrowthChart,
        ])->layout('components.layouts.dashboard', [
            'title' => 'Bảng điều khiển',
        ]);
    }

    protected function syncPresetRange(): void
    {
        [$startDate, $endDate] = $this->dateRangeForPreset($this->timeFilter);

        $this->startDate = $startDate->toDateString();
        $this->endDate = $endDate->toDateString();
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function dateRange(): array
    {
        if ($this->timeFilter === 'custom' && filled($this->startDate) && filled($this->endDate)) {
            $startDate = CarbonImmutable::parse($this->startDate)->startOfDay();
            $endDate = CarbonImmutable::parse($this->endDate)->endOfDay();

            if ($startDate->greaterThan($endDate)) {
                [$startDate, $endDate] = [$endDate->startOfDay(), $startDate->endOfDay()];
            }

            return [$startDate, $endDate];
        }

        return $this->dateRangeForPreset($this->timeFilter);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function dateRangeForPreset(string $timeFilter): array
    {
        $today = CarbonImmutable::now();

        return match ($timeFilter) {
            'today'         => [$today->startOfDay(), $today->endOfDay()],
            'last_7_days'   => [$today->subDays(6)->startOfDay(), $today->endOfDay()],
            'month_to_date' => [$today->startOfMonth()->startOfDay(), $today->endOfDay()],
            'year_to_date'  => [$today->startOfYear()->startOfDay(), $today->endOfDay()],
            default         => [$today->subDays(29)->startOfDay(), $today->endOfDay()],
        };
    }

    protected function rangeLabel(CarbonImmutable $startDate, CarbonImmutable $endDate): string
    {
        return $startDate->isSameDay($endDate)
            ? $startDate->format('d/m/Y')
            : $startDate->format('d/m/Y').' - '.$endDate->format('d/m/Y');
    }
}
