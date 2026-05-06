<div class="space-y-6">
    @php
        $libraryUrl = \Illuminate\Support\Facades\Route::has('app.library.show')
            ? route('app.library.show')
            : url('/my-library');

        $complaintsUrl = \Illuminate\Support\Facades\Route::has('seller.complaints.index')
            ? route('seller.complaints.index')
            : url('/seller/complaints');

        $withdrawalsUrl = \Illuminate\Support\Facades\Route::has('seller.withdrawals.index')
            ? route('seller.withdrawals.index')
            : url('/seller/withdrawals');
    @endphp

    @if(session('seller-status'))
        <div class="rounded-[1.75rem] border border-emerald-500/15 bg-emerald-500/8 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
            {{ session('seller-status') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3">
                <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-[#FCF9F4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                    Dashboard người bán
                </p>
                <div>
                    <h2 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">Xin chào, {{ $seller->shop_name }}</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Theo dõi doanh thu, số đơn, phí nền tảng, khiếu nại và top sản phẩm bán chạy trong {{ $rangeLabel }}.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('seller.products.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                        Quản lý sản phẩm
                    </a>
                    <a href="{{ route('seller.listings.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                        Quản lý listing
                    </a>
                    <a href="{{ $complaintsUrl }}" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                        Khiếu nại
                    </a>
                    <a href="{{ $withdrawalsUrl }}" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                        Rút tiền
                    </a>
                    <a href="{{ $libraryUrl }}" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                        Thư viện của tôi
                    </a>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Trạng thái KYC</p>
                    <div class="mt-3 inline-flex rounded-full px-3 py-1 text-[11px] font-semibold {{ $portalStatus['badgeClass'] }}">{{ $portalStatus['badge'] }}</div>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Người bán từ {{ $seller->created_at?->format('d/m/Y') ?? '--' }}</p>
                </div>

                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Khoảng thời gian</p>
                    <p class="mt-3 text-sm font-semibold text-gray-950 dark:text-white">{{ $rangeLabel }}</p>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Mặc định 30 ngày gần nhất.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Doanh thu</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['sellerEarnings'], 0, ',', '.') }} VND</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Phí nền tảng {{ number_format($metrics['platformFee'], 0, ',', '.') }} VND</p>
        </article>

        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Số đơn</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['orders']) }}</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ number_format($metrics['itemsSold']) }} sản phẩm/keys đã bán</p>
        </article>

        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Tỷ lệ khiếu nại</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['complaintRate'], 2, ',', '.') }}%</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ number_format($metrics['complaints']) }} khiếu nại trong kỳ</p>
        </article>

        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Khiếu nại mở</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['openComplaints'] + $metrics['inProcessComplaints'] + $metrics['escalatedComplaints']) }}</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ number_format($metrics['resolvedComplaints']) }} đã xử lý xong</p>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)]">
        <article class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Doanh thu và số đơn theo thời gian</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Đường doanh thu và đơn hàng trong {{ $rangeLabel }}.</p>
                </div>
            </div>

            <div class="mt-6" x-data="sellerRevenueOrdersChart()" x-init="render(@js($revenueOrdersChart)); $watch('$wire.revenueOrdersChart', (value) => update(value));">
                <div class="relative h-96 min-h-96 w-full" wire:ignore>
                    <div x-ref="chart" class="h-full w-full"></div>
                </div>
            </div>
        </article>

        <article class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
            <div>
                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Top sản phẩm bán chạy</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Các listing/sản phẩm có số lượng bán cao nhất trong kỳ.</p>
            </div>

            <div class="mt-6" x-data="sellerTopProductsChart()" x-init="render(@js($topProductsChart)); $watch('$wire.topProductsChart', (value) => update(value));">
                <div class="relative h-96 min-h-96 w-full" wire:ignore>
                    <div x-ref="chart" class="h-full w-full"></div>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
        <article class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Bảng top sản phẩm</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Xem nhanh sản phẩm nào đang kéo doanh thu tốt nhất.</p>
                </div>
            </div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-black/8 dark:border-white/10">
                <div class="grid grid-cols-12 gap-3 border-b border-black/8 bg-[#FCF9F4] px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                    <div class="col-span-6">Sản phẩm</div>
                    <div class="col-span-2 text-right">Đã bán</div>
                    <div class="col-span-2 text-right">Đơn</div>
                    <div class="col-span-2 text-right">Doanh thu</div>
                </div>

                <div class="divide-y divide-black/8 dark:divide-white/10">
                    @forelse($topProducts as $product)
                        <div class="grid grid-cols-12 gap-3 px-4 py-4 text-sm">
                            <div class="col-span-6 min-w-0">
                                <p class="truncate font-semibold text-gray-950 dark:text-white">{{ $product['label'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sản phẩm bán chạy trong {{ $rangeLabel }}</p>
                            </div>
                            <div class="col-span-2 text-right font-semibold text-gray-950 dark:text-white">{{ number_format($product['quantity_sold']) }}</div>
                            <div class="col-span-2 text-right text-gray-600 dark:text-gray-300">{{ number_format($product['orders_count']) }}</div>
                            <div class="col-span-2 text-right text-gray-600 dark:text-gray-300">{{ number_format($product['revenue'], 0, ',', '.') }} VND</div>
                        </div>
                    @empty
                        <div class="px-4 py-8 text-sm text-gray-500 dark:text-gray-400">Chưa có dữ liệu bán hàng trong kỳ.</div>
                    @endforelse
                </div>
            </div>
        </article>

        <article class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
            <div>
                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Tình trạng khiếu nại</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tổng hợp theo trạng thái để ưu tiên xử lý.</p>
            </div>

            <div class="mt-5 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3 dark:bg-white/5">
                    <span class="text-gray-600 dark:text-gray-300">Mở</span>
                    <span class="font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['openComplaints']) }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3 dark:bg-white/5">
                    <span class="text-gray-600 dark:text-gray-300">Đang xử lý</span>
                    <span class="font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['inProcessComplaints']) }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3 dark:bg-white/5">
                    <span class="text-gray-600 dark:text-gray-300">Đã escalated</span>
                    <span class="font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['escalatedComplaints']) }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3 dark:bg-white/5">
                    <span class="text-gray-600 dark:text-gray-300">Đã xử lý</span>
                    <span class="font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['resolvedComplaints']) }}</span>
                </div>
            </div>
        </article>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Sản phẩm gần đây</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Các sản phẩm bạn gửi gần đây và trạng thái catalog.</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($recentProducts as $product)
                    <article class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-3 dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h4 class="font-semibold text-gray-950 dark:text-white">{{ $product->name }}</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $product->variants_count }} biến thể · {{ $product->listings_count }} listing</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $product->status->value === \App\Enums\GeneralStatus::Active->value ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-slate-500/10 text-slate-700 dark:text-slate-300' }}">{{ $product->status->label() }}</span>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-black/15 px-4 py-6 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                        Chưa có sản phẩm.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Listing gần đây</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Theo dõi tồn kho đang gắn với cửa hàng của bạn.</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($recentListings as $listing)
                    @php
                        $listingStatusClass = match ($listing->status) {
                            \App\Enums\ProductListingStatus::Active => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                            \App\Enums\ProductListingStatus::Pending => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                            \App\Enums\ProductListingStatus::Rejected => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                            default => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
                        };
                    @endphp

                    <article class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-3 dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h4 class="font-semibold text-gray-950 dark:text-white">{{ $listing->display_name ?: $listing->variant?->product?->name }}</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $listing->variant?->product?->name ?? 'Sản phẩm' }} · {{ $listing->variant?->edition ?? 'Bản tiêu chuẩn' }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $listingStatusClass }}">{{ $listing->status->label() }}</span>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">
                            <span class="rounded-full border border-black/8 bg-white px-3 py-1 dark:border-white/10 dark:bg-gray-950">{{ number_format((float) $listing->price, 0, ',', '.') }} VND</span>
                            <span class="rounded-full border border-black/8 bg-white px-3 py-1 dark:border-white/10 dark:bg-gray-950">{{ $listing->available_keys_count }} key</span>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-black/15 px-4 py-6 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                        Chưa có listing.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @push('scripts')
        @once
        <script>
            const sellerDashboardCurrencyFormatter = new Intl.NumberFormat('vi-VN');

            window.sellerDashboardTheme = function () {
                const isDark = document.documentElement.classList.contains('dark') || document.documentElement.dataset.theme === 'dark';

                return {
                    isDark,
                    labelColor: isDark ? '#94a3b8' : '#64748b',
                    gridColor: isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(15, 23, 42, 0.08)',
                };
            };

            window.sellerDashboardNoData = function () {
                const theme = window.sellerDashboardTheme();

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

            window.sellerRevenueOrdersChart = function () {
                return {
                    chart: null,
                    buildOptions(payload) {
                        const theme = window.sellerDashboardTheme();

                        return {
                            chart: {
                                type: 'line',
                                height: '100%',
                                width: '100%',
                                toolbar: { show: false },
                                foreColor: theme.labelColor,
                            },
                            noData: window.sellerDashboardNoData(),
                            colors: ['#D32F2F', '#111827'],
                            stroke: {
                                width: [3, 3],
                                curve: 'smooth',
                            },
                            series: [
                                { name: 'Doanh thu', data: payload.revenue ?? [] },
                                { name: 'Số đơn', data: payload.orders ?? [] },
                            ],
                            xaxis: {
                                categories: payload.labels ?? [],
                                labels: { style: { colors: theme.labelColor } },
                            },
                            yaxis: [
                                {
                                    labels: {
                                        style: { colors: theme.labelColor },
                                        formatter(value) {
                                            return `${sellerDashboardCurrencyFormatter.format(value ?? 0)} VND`;
                                        },
                                    },
                                },
                                {
                                    opposite: true,
                                    labels: {
                                        style: { colors: theme.labelColor },
                                        formatter(value) {
                                            return sellerDashboardCurrencyFormatter.format(value ?? 0);
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
                                        return seriesIndex === 0
                                            ? `${sellerDashboardCurrencyFormatter.format(value ?? 0)} VND`
                                            : `${sellerDashboardCurrencyFormatter.format(value ?? 0)} đơn`;
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

            window.sellerTopProductsChart = function () {
                return {
                    chart: null,
                    buildOptions(payload) {
                        const theme = window.sellerDashboardTheme();

                        return {
                            chart: {
                                type: 'bar',
                                height: '100%',
                                width: '100%',
                                toolbar: { show: false },
                                foreColor: theme.labelColor,
                            },
                            noData: window.sellerDashboardNoData(),
                            series: [{ name: 'Đã bán', data: payload.values ?? [] }],
                            colors: ['#D32F2F'],
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
                                        return `${sellerDashboardCurrencyFormatter.format(value ?? 0)} key`;
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
