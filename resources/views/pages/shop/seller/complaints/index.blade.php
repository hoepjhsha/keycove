<div class="space-y-6">
    <section class="overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3">
                <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-[#FCF9F4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                    Khiếu nại
                </p>
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">Khiếu nại của người bán</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">Theo dõi các cuộc trao đổi khiếu nại với sản phẩm bán từ cửa hàng và phản hồi bằng bằng chứng khi cần.</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('seller.dashboard.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                        Quay lại dashboard
                    </a>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Đang mở</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $openCount }}</p>
                </div>
                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Đang xử lý</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $activeCount }}</p>
                </div>
                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Đã xử lý</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $resolvedCount }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        @forelse($complaints as $complaint)
            @php
                $statusClass = match ($complaint->status) {
                    \App\Enums\ComplaintStatus::Open => 'bg-blue-500/10 text-blue-700 dark:text-blue-300',
                    \App\Enums\ComplaintStatus::InProcess => 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-300',
                    \App\Enums\ComplaintStatus::Escalated => 'bg-orange-500/10 text-orange-700 dark:text-orange-300',
                    \App\Enums\ComplaintStatus::ApprovedRefund => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                    \App\Enums\ComplaintStatus::RejectedRelease => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
                    default => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
                };
            @endphp

            <article wire:key="seller-complaint-{{ $complaint->id }}" class="rounded-[2rem] border border-black/8 bg-white/90 p-5 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-black px-3 py-1 text-[11px] font-semibold text-white dark:bg-white dark:text-gray-950">{{ $complaint->complaint_code ?? ('#'.$complaint->id) }}</span>
                            <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $statusClass }}">{{ $complaint->status->label() }}</span>
                        </div>

                        <div>
                            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $complaint->orderItem?->product_name_snapshot ?? 'Sản phẩm' }}</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Người mua: {{ $complaint->orderItem?->order?->buyer?->username ?? '-' }} · Đơn hàng: {{ $complaint->orderItem?->order?->order_code ?? '-' }}</p>
                        </div>

                        <p class="text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $complaint->reason }}</p>
                    </div>

                    <div class="flex flex-col items-start gap-2 lg:items-end">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Cập nhật</p>
                        <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $complaint->updated_at?->format('d/m/Y H:i') ?? '--' }}</p>
                        @php
                            $complaintRouteValue = $complaint->complaint_code ?: $complaint->id;
                        @endphp

                        <a href="{{ route('seller.complaints.show', ['complaint' => $complaintRouteValue]) }}" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                            Mở hội thoại
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-[2rem] border border-dashed border-black/15 bg-white/90 p-10 text-center shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Chưa có khiếu nại</h2>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Các tranh chấp liên quan đến listing của bạn sẽ xuất hiện tại đây.</p>
            </div>
        @endforelse
    </section>
</div>
