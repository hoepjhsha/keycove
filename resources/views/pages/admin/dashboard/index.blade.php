<div class="space-y-6">
    @section('pageTitle', __('admin.titles.dashboard'))

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => __('admin.nav.dashboard'), 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <section class="rounded-3xl border border-slate-200 bg-linear-to-br from-white via-slate-50 to-sky-50/70 p-6 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="space-y-3">
                <span class="inline-flex w-fit items-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                    KeyCove Admin Dashboard
                </span>

                <div>
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Doanh thu, đơn hàng, seller và complaint</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-400">
                        Dashboard này tập trung vào doanh thu nền tảng, nhịp đơn hàng, hàng bán chạy, thể loại được ưu thích, seller dẫn đầu và tín hiệu complaint trong giai đoạn <span class="font-semibold text-slate-900 dark:text-white">{{ $rangeLabel }}</span>.
                    </p>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-4 xl:min-w-[34rem]">
                <label class="block md:col-span-2 xl:col-span-2">
                    <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Khoảng thời gian</span>
                    <select wire:model.live="timeFilter" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                        <option value="today">Hôm nay</option>
                        <option value="last_7_days">7 ngày gần nhất</option>
                        <option value="last_30_days">30 ngày gần nhất</option>
                        <option value="month_to_date">Tháng này</option>
                        <option value="year_to_date">Năm nay</option>
                        <option value="custom">Tùy chỉnh</option>
                    </select>
                </label>

                @if($timeFilter === 'custom')
                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Từ ngày</span>
                        <input type="date" wire:model.live="startDate" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Đến ngày</span>
                        <input type="date" wire:model.live="endDate" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                    </label>
                @endif
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-4">
        <article class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">Doanh thu nền tảng</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-900 dark:text-white">{{ number_format($revenue['platformRevenue'], 0, ',', '.') }} VND</p>
            <div class="mt-3 space-y-2 text-sm text-emerald-800/90 dark:text-emerald-100/80">
                <div class="flex items-center justify-between gap-4">
                    <span>Platform fee</span>
                    <span class="font-semibold">{{ number_format($revenue['platformFeeRevenue'], 0, ',', '.') }} VND</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span>Product owned</span>
                    <span class="font-semibold">{{ number_format($revenue['platformOwnedRevenue'], 0, ',', '.') }} VND</span>
                </div>
                <div class="flex items-center justify-between gap-4 border-t border-emerald-200/70 pt-2 dark:border-emerald-500/20">
                    <span>Take rate</span>
                    <span class="font-semibold">{{ number_format($revenue['platformTakeRate'], 2, ',', '.') }}%</span>
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Lượng đơn</p>
            <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($orders['ordersCount']) }}</p>
            <div class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                <div class="flex items-center justify-between gap-4">
                    <span>Revenue items</span>
                    <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($orders['revenueItemsCount']) }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span>AOV</span>
                    <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($orders['averageOrderValue'], 0, ',', '.') }} VND</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span>Revenue / order</span>
                    <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($orders['revenuePerOrder'], 0, ',', '.') }} VND</span>
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-rose-200 bg-rose-50 p-5 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/10">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700 dark:text-rose-300">Complaint billboard</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl bg-white/80 p-4 dark:bg-slate-950/50">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Shop Admin</p>
                    <p class="mt-2 text-2xl font-semibold text-rose-700 dark:text-rose-300">{{ number_format($complaints['shopAdminComplaintRate'], 2, ',', '.') }}%</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($complaints['shopAdminComplaintCount']) }} complaint / {{ number_format($complaints['shopAdminRevenueItems']) }} items</p>
                </div>
                <div class="rounded-2xl bg-white/80 p-4 dark:bg-slate-950/50">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Seller</p>
                    <p class="mt-2 text-2xl font-semibold text-rose-700 dark:text-rose-300">{{ number_format($complaints['sellerComplaintRate'], 2, ',', '.') }}%</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($complaints['sellerComplaintCount']) }} complaint / {{ number_format($complaints['sellerRevenueItems']) }} items</p>
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-sky-200 bg-white p-5 shadow-sm dark:border-sky-500/20 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700 dark:text-sky-300">Top signal</p>
            <div class="mt-3 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Thể loại hot nhất</p>
                    <p class="mt-1 font-semibold text-slate-950 dark:text-white">{{ data_get($highlights, 'topCategory.category_name', 'Chưa có dữ liệu') }}</p>
                    <p class="text-xs">{{ number_format((int) data_get($highlights, 'topCategory.units_sold', 0)) }} units</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Seller dẫn đầu</p>
                    <p class="mt-1 font-semibold text-slate-950 dark:text-white">{{ data_get($highlights, 'topSeller.seller_name', 'Chưa có dữ liệu') }}</p>
                    <p class="text-xs">{{ number_format((float) data_get($highlights, 'topSeller.gross_revenue', 0), 0, ',', '.') }} VND</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Sản phẩm dẫn đầu</p>
                    <p class="mt-1 font-semibold text-slate-950 dark:text-white">{{ data_get($highlights, 'topProduct.product_name', 'Chưa có dữ liệu') }}</p>
                    <p class="text-xs">{{ number_format((int) data_get($highlights, 'topProduct.units_sold', 0)) }} units</p>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Doanh thu nền tảng và lượng đơn theo thời gian</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Cho thấy nguồn doanh thu đang đến từ đâu và nhịp đơn hàng biến động thế nào.</p>
            </div>

            <div class="mt-6" x-data="revenueOrdersChart()" x-init="render(@js($revenueOrdersChart)); $watch('$wire.revenueOrdersChart', (value) => update(value));">
                <div class="relative h-96 min-h-96 w-full" wire:ignore>
                    <div x-ref="chart" class="h-full w-full"></div>
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Người dùng thích thể loại nào</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Dựa trên lượng sold units của category trong khoảng đã chọn.</p>
            </div>

            <div class="mt-6" x-data="categoryPreferencesChart()" x-init="render(@js($categoryPreferencesChart)); $watch('$wire.categoryPreferencesChart', (value) => update(value));">
                <div class="relative h-96 min-h-96 w-full" wire:ignore>
                    <div x-ref="chart" class="h-full w-full"></div>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Seller bán chạy nhất</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Xếp theo gross revenue, có kèm complaint rate để nhìn cả doanh số lẫn rủi ro.</p>
            </div>

            <div class="mt-6" x-data="sellerRevenueChart()" x-init="render(@js($sellerRevenueChart)); $watch('$wire.sellerRevenueChart', (value) => update(value));">
                <div class="relative h-96 min-h-96 w-full" wire:ignore>
                    <div x-ref="chart" class="h-full w-full"></div>
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Top sản phẩm chung</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Hàng nào đang bán chạy nhất trên toàn nền tảng.</p>
                </div>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-950/40 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Sản phẩm</th>
                            <th class="px-4 py-3 text-right">Units</th>
                            <th class="px-4 py-3 text-right">Revenue</th>
                            <th class="px-4 py-3 text-right">Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($products['topProducts'] as $product)
                            <tr class="text-sm text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-950 dark:text-white">{{ $product['product_name'] }}</p>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">{{ number_format($product['units_sold']) }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ number_format($product['revenue'], 0, ',', '.') }} VND</td>
                                <td class="px-4 py-3 text-right text-slate-500 dark:text-slate-400">{{ number_format($product['seller_net_revenue'], 0, ',', '.') }} VND</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Chưa có dữ liệu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Sản phẩm seller đang bán chạy</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sản phẩm nào của seller đang tạo doanh số tốt nhất.</p>
                </div>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-950/40 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Seller</th>
                            <th class="px-4 py-3">Sản phẩm</th>
                            <th class="px-4 py-3 text-right">Units</th>
                            <th class="px-4 py-3 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($products['topSellerProducts'] as $product)
                            <tr class="text-sm text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3 font-semibold text-slate-950 dark:text-white">{{ $product['seller_name'] }}</td>
                                <td class="px-4 py-3">{{ $product['product_name'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ number_format($product['units_sold']) }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ number_format($product['revenue'], 0, ',', '.') }} VND</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Chưa có dữ liệu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Seller performance</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Doanh số seller, platform fee và complaint rate.</p>
                </div>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-950/40 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Seller</th>
                            <th class="px-4 py-3 text-right">Gross</th>
                            <th class="px-4 py-3 text-right">Orders</th>
                            <th class="px-4 py-3 text-right">Complaint</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($sellers['topSellers'] as $seller)
                            <tr class="text-sm text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-950 dark:text-white">{{ $seller['seller_name'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($seller['units_sold']) }} units</p>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">{{ number_format($seller['gross_revenue'], 0, ',', '.') }} VND</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ number_format($seller['orders_count']) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="rounded-full bg-rose-500/10 px-3 py-1 text-xs font-semibold text-rose-700 dark:text-rose-300">
                                        {{ number_format($seller['complaint_rate'], 2, ',', '.') }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Chưa có dữ liệu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Seller có complaint rate cao</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Billboard để thấy seller nào đang có rủi ro vận hành.</p>
                </div>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-950/40 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Seller</th>
                            <th class="px-4 py-3 text-right">Complaint</th>
                            <th class="px-4 py-3 text-right">Items</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($sellers['worstComplaintRates'] as $seller)
                            <tr class="text-sm text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-950 dark:text-white">{{ $seller['seller_name'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($seller['gross_revenue'], 0, ',', '.') }} VND gross</p>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="rounded-full bg-rose-500/10 px-3 py-1 text-xs font-semibold text-rose-700 dark:text-rose-300">{{ number_format($seller['complaint_rate'], 2, ',', '.') }}%</span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">{{ number_format($seller['complaint_count']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Chưa có dữ liệu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

    </section>

    <livewire:admin.action.dashboard.dashboard-ai-assistant
        :context="$dashboardAiContext"
        :range-label="$rangeLabel"
        :key="'dashboard-ai-'.$timeFilter.'-'.$startDate.'-'.$endDate"
    />

    @push('scripts')
        @once
        <script>
            const adminDashboardCurrencyFormatter = new Intl.NumberFormat('vi-VN');

            window.adminDashboardTheme = function () {
                const isDark = document.documentElement.dataset.theme === 'dark';

                return {
                    isDark,
                    labelColor: isDark ? '#94a3b8' : '#64748b',
                    gridColor: isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(15, 23, 42, 0.08)',
                };
            };

            window.adminDashboardNoData = function () {
                const theme = window.adminDashboardTheme();

                return {
                    text: 'Không có dữ liệu trong khoảng thời gian này',
                    align: 'center',
                    verticalAlign: 'middle',
                    style: {
                        color: theme.labelColor,
                        fontSize: '14px',
                    },
                };
            };

            window.revenueOrdersChart = function () {
                return {
                    chart: null,
                    buildOptions(payload) {
                        const theme = window.adminDashboardTheme();

                        return {
                            chart: {
                                type: 'line',
                                height: '100%',
                                width: '100%',
                                toolbar: { show: false },
                                foreColor: theme.labelColor,
                            },
                            noData: window.adminDashboardNoData(),
                            colors: ['#059669', '#2563eb'],
                            stroke: {
                                width: [3, 3],
                                curve: 'smooth',
                            },
                            series: [
                                { name: 'Doanh thu nền tảng', type: 'line', data: payload.revenue ?? [] },
                                { name: 'Số đơn', type: 'line', data: payload.orders ?? [] },
                            ],
                            xaxis: {
                                categories: payload.labels ?? [],
                                labels: { style: { colors: theme.labelColor } },
                            },
                            yaxis: [
                                {
                                    seriesName: 'Doanh thu nền tảng',
                                    labels: {
                                        style: { colors: theme.labelColor },
                                        formatter(value) {
                                            return `${adminDashboardCurrencyFormatter.format(value ?? 0)} VND`;
                                        },
                                    },
                                },
                                {
                                    seriesName: 'Số đơn',
                                    opposite: true,
                                    labels: {
                                        style: { colors: theme.labelColor },
                                        formatter(value) {
                                            return adminDashboardCurrencyFormatter.format(value ?? 0);
                                        },
                                    },
                                },
                            ],
                            grid: { borderColor: theme.gridColor },
                            legend: { labels: { colors: theme.labelColor } },
                            dataLabels: { enabled: false },
                            tooltip: {
                                theme: theme.isDark ? 'dark' : 'light',
                                y: {
                                    formatter(value, { seriesIndex }) {
                                        return seriesIndex === 1
                                            ? `${adminDashboardCurrencyFormatter.format(value ?? 0)} đơn`
                                            : `${adminDashboardCurrencyFormatter.format(value ?? 0)} VND`;
                                    },
                                },
                            },
                        };
                    },
                    render(payload) {
                        if (!window.ApexCharts || !this.$refs.chart) {
                            return;
                        }

                        this.chart?.destroy();
                        this.chart = new window.ApexCharts(this.$refs.chart, this.buildOptions(payload));

                        setTimeout(() => this.chart?.render(), 50);
                    },
                    update(payload) {
                        if (!this.chart) {
                            this.render(payload);
                            return;
                        }

                        this.chart.updateOptions(this.buildOptions(payload));
                    },
                };
            };

            window.categoryPreferencesChart = function () {
                return {
                    chart: null,
                    buildOptions(payload) {
                        const theme = window.adminDashboardTheme();

                        return {
                            chart: {
                                type: 'bar',
                                height: '100%',
                                width: '100%',
                                toolbar: { show: false },
                                foreColor: theme.labelColor,
                            },
                            noData: window.adminDashboardNoData(),
                            series: [{ name: 'Units', data: payload.quantities ?? [] }],
                            colors: ['#0ea5e9'],
                            plotOptions: {
                                bar: {
                                    borderRadius: 10,
                                    horizontal: true,
                                },
                            },
                            dataLabels: { enabled: false },
                            xaxis: {
                                categories: payload.labels ?? [],
                                labels: { style: { colors: theme.labelColor } },
                            },
                            yaxis: {
                                labels: { style: { colors: theme.labelColor } },
                            },
                            grid: { borderColor: theme.gridColor },
                            tooltip: {
                                theme: theme.isDark ? 'dark' : 'light',
                                y: {
                                    formatter(value) {
                                        return `${adminDashboardCurrencyFormatter.format(value ?? 0)} units`;
                                    },
                                },
                            },
                        };
                    },
                    render(payload) {
                        if (!window.ApexCharts || !this.$refs.chart) {
                            return;
                        }

                        this.chart?.destroy();
                        this.chart = new window.ApexCharts(this.$refs.chart, this.buildOptions(payload));

                        setTimeout(() => this.chart?.render(), 50);
                    },
                    update(payload) {
                        if (!this.chart) {
                            this.render(payload);
                            return;
                        }

                        this.chart.updateOptions(this.buildOptions(payload));
                    },
                };
            };

            window.sellerRevenueChart = function () {
                return {
                    chart: null,
                    buildOptions(payload) {
                        const theme = window.adminDashboardTheme();

                        return {
                            chart: {
                                type: 'bar',
                                height: '100%',
                                width: '100%',
                                toolbar: { show: false },
                                foreColor: theme.labelColor,
                            },
                            noData: window.adminDashboardNoData(),
                            series: [{ name: 'Gross revenue', data: payload.grossRevenue ?? [] }],
                            colors: ['#7c3aed'],
                            plotOptions: {
                                bar: {
                                    borderRadius: 10,
                                    horizontal: true,
                                },
                            },
                            dataLabels: { enabled: false },
                            xaxis: {
                                categories: payload.labels ?? [],
                                labels: { style: { colors: theme.labelColor } },
                            },
                            yaxis: {
                                labels: { style: { colors: theme.labelColor } },
                            },
                            grid: { borderColor: theme.gridColor },
                            tooltip: {
                                theme: theme.isDark ? 'dark' : 'light',
                                y: {
                                    formatter(value) {
                                        return `${adminDashboardCurrencyFormatter.format(value ?? 0)} VND`;
                                    },
                                },
                            },
                        };
                    },
                    render(payload) {
                        if (!window.ApexCharts || !this.$refs.chart) {
                            return;
                        }

                        this.chart?.destroy();
                        this.chart = new window.ApexCharts(this.$refs.chart, this.buildOptions(payload));

                        setTimeout(() => this.chart?.render(), 50);
                    },
                    update(payload) {
                        if (!this.chart) {
                            this.render(payload);
                            return;
                        }

                        this.chart.updateOptions(this.buildOptions(payload));
                    },
                };
            };
        </script>
        @endonce
    @endpush
</div>
