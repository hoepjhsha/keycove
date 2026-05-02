<div class="space-y-6">
    @section('pageTitle', __('admin.titles.dashboard'))

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => __('admin.nav.dashboard'), 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-linear-to-br from-white via-slate-50 to-sky-50/70 p-6 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="space-y-3">
                <span class="inline-flex w-fit items-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                    KeyCove Admin Intelligence
                </span>

                <div>
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Toàn cảnh tài chính, vận hành và tăng trưởng của KeyCove</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-400">
                        Dashboard này tập trung vào GMV, doanh thu sàn, điểm nghẽn vận hành, sức khỏe thị trường và tốc độ tăng trưởng trong giai đoạn <span class="font-semibold text-slate-900 dark:text-white">{{ $rangeLabel }}</span>.
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

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">GMV</p>
            <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($quickStats['gmv'], 0, ',', '.') }} VND</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Tổng giá trị giao dịch của các đơn đã thanh toán thành công.</p>
        </article>

        <article class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">Doanh thu sàn</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-900 dark:text-white">{{ number_format($quickStats['netRevenue'], 0, ',', '.') }} VND</p>
            <p class="mt-2 text-sm text-emerald-700/80 dark:text-emerald-200/80">Tổng phí nền tảng thu được từ các đơn ghi nhận doanh thu.</p>
        </article>

        <article class="rounded-3xl border border-rose-200 bg-rose-50 p-5 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/10">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700 dark:text-rose-300">Khiếu nại mở</p>
            <p class="mt-3 text-3xl font-semibold text-rose-900 dark:text-white">{{ number_format($quickStats['openComplaints']) }}</p>
            <p class="mt-2 text-sm text-rose-700/80 dark:text-rose-200/80">Những tranh chấp đang cần đội ngũ admin xử lý.</p>
        </article>

        <article class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">Seller chờ duyệt</p>
            <p class="mt-3 text-3xl font-semibold text-amber-900 dark:text-white">{{ number_format($quickStats['pendingKycSeller']) }}</p>
            <p class="mt-2 text-sm text-amber-700/80 dark:text-amber-200/80">Hàng đợi KYC cần được phê duyệt để mở rộng nguồn cung.</p>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(0,1fr)_22rem]">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">GMV và số đơn theo thời gian</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Cho thấy quy mô giao dịch và nhịp hoạt động của nền tảng trong giai đoạn đã chọn.</p>
                </div>
            </div>

            <div class="mt-6" x-data="revenueOrdersChart()" x-init="
                render(@js($revenueOrdersChart));
                $watch('$wire.revenueOrdersChart', (value) => update(value));
            ">
                <div class="relative h-96 min-h-96 w-full" wire:ignore>
                    <div x-ref="chart" class="h-full w-full"></div>
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Doanh thu theo nền tảng</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Nền tảng nào đang mang lại giá trị giao dịch cao nhất cho KeyCove.</p>
            </div>

            <div class="mt-6" x-data="platformRevenueChart()" x-init="
                render(@js($platformRevenueChart));
                $watch('$wire.platformRevenueChart', (value) => update(value));
            ">
                <div class="relative h-96 min-h-96 w-full" wire:ignore>
                    <div x-ref="chart" class="h-full w-full"></div>
                </div>
            </div>
        </article>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Nhật ký hệ thống</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Theo dõi các thay đổi và hành động gần nhất trên nền tảng.</p>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($auditLogs as $log)
                    @php
                        $auditTimestamp = is_int($log->created_at)
                            ? Illuminate\Support\Carbon::createFromTimestamp($log->created_at)
                            : $log->created_at;
                    @endphp
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-950 dark:text-white">{{ $log->user?->username ?? 'Hệ thống' }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $log->event->label() }} · {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</p>
                            </div>
                            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $auditTimestamp?->format('d/m H:i') }}</span>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        Chưa có bản ghi audit nào.
                    </div>
                @endforelse
            </div>
        </aside>
    </section>

    <section class="grid gap-6 xl:grid-cols-2 2xl:grid-cols-4">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Dòng tiền sàn</h3>
            <div class="mt-5 space-y-4 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Số dư escrow đang giữ</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($finance['escrowHolding'], 0, ',', '.') }} VND</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Ví nội bộ</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($finance['internalWalletBalance'], 0, ',', '.') }} VND</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Đã chi trả cho seller</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($finance['completedWithdrawals'], 0, ',', '.') }} VND</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Yêu cầu rút tiền pending</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($finance['pendingWithdrawals'], 0, ',', '.') }} VND</span>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3 text-xs text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                    {{ number_format($finance['completedWithdrawalCount']) }} yêu cầu đã hoàn tất · {{ number_format($finance['pendingWithdrawalCount']) }} yêu cầu đang chờ xử lý.
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Điểm nóng vận hành</h3>
            <div class="mt-5 space-y-4 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Tỷ lệ tranh chấp</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($operations['disputeRate'], 2, ',', '.') }}%</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">KYC pending</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($operations['pendingKyc']) }}</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Thời gian xử lý khiếu nại TB</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($operations['avgResolutionHours'], 2, ',', '.') }} giờ</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Listing chờ duyệt</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($operations['pendingListings']) }}</span>
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Thị trường & tồn kho</h3>
            <div class="mt-5 space-y-4 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Key sẵn sàng bán</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($market['availableKeys']) }}</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Listing hoạt động</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($market['activeListings']) }}</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Key đã hoàn tiền</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($market['refundedKeys']) }}</span>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Top Region</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse($market['topRegions'] as $region)
                            <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                                {{ $region['name'] }} · {{ number_format($region['revenue'], 0, ',', '.') }}
                            </span>
                        @empty
                            <span class="text-sm text-slate-500 dark:text-slate-400">Chưa có dữ liệu doanh thu theo khu vực.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Người dùng & tăng trưởng</h3>
            <div class="mt-5 space-y-4 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Người dùng mới</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($growth['newUsers']) }}</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Seller mới</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($growth['newSellers']) }}</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-500 dark:text-slate-400">Điểm đánh giá trung bình</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($growth['avgReviewRating'], 2, ',', '.') }}/5</span>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3 text-xs text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                    Chỉ số này phản ánh tốc độ mở rộng cộng đồng và mức hài lòng chung của người mua trên KeyCove.
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">5 đơn giá trị cao nhất gần đây</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ưu tiên theo tổng giá trị đơn trong giai đoạn đã lọc.</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($topOrders as $order)
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/50">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-950 dark:text-white">#{{ $order->order_code }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $order->buyer?->username ?? '-' }} · {{ $order->items_count }} item · {{ $order->created_at?->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ number_format((float) $order->total_price, 0, ',', '.') }} VND</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $order->payment_status->label() }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        Chưa có đơn hàng trong khoảng thời gian này.
                    </div>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">5 khiếu nại cần xử lý gấp</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ưu tiên các khiếu nại đang mở và tồn đọng lâu nhất.</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($urgentComplaints as $complaint)
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/50">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-950 dark:text-white">{{ $complaint->complaint_code ?? 'Complaint #'.$complaint->id }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $complaint->orderItem?->order?->buyer?->username ?? '-' }} · {{ $complaint->orderItem?->seller?->shop_name ?? 'Shop Admin' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-semibold text-rose-600 dark:text-rose-300">{{ $complaint->status->label() }}</p>
                                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $complaint->created_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                        <p class="mt-3 line-clamp-2 text-sm text-slate-600 dark:text-slate-400">{{ $complaint->reason }}</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        Không có khiếu nại khẩn cấp cần xử lý.
                    </div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Người bán tích cực nhất</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Dựa trên số lượng đơn thành công và doanh thu họ tạo ra cho nền tảng.</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($topSellers as $seller)
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/50">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-950 dark:text-white">{{ $seller->shop_name }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ number_format((int) $seller->successful_orders) }} đơn thành công</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ number_format((float) $seller->gross_revenue, 0, ',', '.') }} VND</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Phí sàn {{ number_format((float) $seller->platform_revenue, 0, ',', '.') }} VND</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        Chưa có dữ liệu seller hoạt động trong khoảng thời gian này.
                    </div>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Tăng trưởng User / Seller</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Nhịp tăng trưởng cộng đồng người mua và nhà bán hàng trên KeyCove.</p>
            </div>

            <div class="mt-6" x-data="userGrowthChart()" x-init="
                render(@js($userGrowthChart));
                $watch('$wire.userGrowthChart', (value) => update(value));
            ">
                <div class="relative h-96 min-h-96 w-full" wire:ignore>
                    <div x-ref="chart" class="h-full w-full"></div>
                </div>
            </div>
        </article>
    </section>

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
                    mutedColor: isDark ? '#475569' : '#cbd5e1',
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
                            colors: ['#0f766e', '#2563eb'],
                            stroke: {
                                width: [3, 3],
                                curve: 'smooth',
                            },
                            series: [
                                { name: 'GMV', data: payload.revenue ?? [] },
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
                                            return `${adminDashboardCurrencyFormatter.format(value ?? 0)} VND`;
                                        },
                                    },
                                },
                                {
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
                                        return seriesIndex === 0
                                            ? `${adminDashboardCurrencyFormatter.format(value ?? 0)} VND`
                                            : `${adminDashboardCurrencyFormatter.format(value ?? 0)} đơn`;
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

            window.platformRevenueChart = function () {
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
                            series: [{
                                name: 'Doanh thu',
                                data: payload.values ?? [],
                            }],
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
                                labels: {
                                    style: { colors: theme.labelColor },
                                    formatter(value) {
                                        return adminDashboardCurrencyFormatter.format(value ?? 0);
                                    },
                                },
                            },
                            yaxis: {
                                labels: { style: { colors: theme.labelColor } },
                            },
                            grid: { borderColor: theme.gridColor },
                            tooltip: {
                                theme: theme.isDark ? 'dark' : 'light',
                                x: { show: false },
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

            window.userGrowthChart = function () {
                return {
                    chart: null,
                    buildOptions(payload) {
                        const theme = window.adminDashboardTheme();

                        return {
                            chart: {
                                type: 'area',
                                height: '100%',
                                width: '100%',
                                toolbar: { show: false },
                                foreColor: theme.labelColor,
                            },
                            noData: window.adminDashboardNoData(),
                            colors: ['#0ea5e9', '#f59e0b'],
                            series: [
                                { name: 'User mới', data: payload.users ?? [] },
                                { name: 'Seller mới', data: payload.sellers ?? [] },
                            ],
                            stroke: { curve: 'smooth', width: 3 },
                            fill: { opacity: 0.16 },
                            dataLabels: { enabled: false },
                            xaxis: {
                                categories: payload.labels ?? [],
                                labels: { style: { colors: theme.labelColor } },
                            },
                            yaxis: {
                                labels: {
                                    style: { colors: theme.labelColor },
                                    formatter(value) {
                                        return adminDashboardCurrencyFormatter.format(value ?? 0);
                                    },
                                },
                            },
                            grid: { borderColor: theme.gridColor },
                            legend: { labels: { colors: theme.labelColor } },
                            tooltip: {
                                theme: theme.isDark ? 'dark' : 'light',
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
