<div class="space-y-6">
    @php
        $complaintsUrl = \Illuminate\Support\Facades\Route::has('seller.complaints.index')
            ? route('seller.complaints.index')
            : url('/seller/complaints');
    @endphp

    <section class="overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3">
                <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-[#FCF9F4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                    Đơn hàng của tôi
                </p>
                <div>
                    <h2 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">Quản lý order item theo seller</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Theo dõi trạng thái xử lý, doanh thu, phí, escrow và khiếu nại trong {{ $rangeLabel }}.
                    </p>
                </div>
            </div>

            <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Khoảng thời gian</p>
                <p class="mt-3 text-sm font-semibold text-gray-950 dark:text-white">{{ $rangeLabel }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Mặc định 30 ngày gần nhất.</p>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Order items</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['totalItems']) }}</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ number_format($metrics['totalOrders']) }} đơn unique</p>
        </article>

        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Doanh thu seller</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['totalRevenue'], 0, ',', '.') }} VND</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Phí nền tảng {{ number_format($metrics['totalFee'], 0, ',', '.') }} VND</p>
        </article>

        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Escrow giữ</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['holdingEscrow'], 0, ',', '.') }} VND</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ number_format($metrics['openComplaints']) }} khiếu nại mở</p>
        </article>
    </section>

    <section x-data="{ filtersOpen: false }" class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
        <article>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Bộ lọc</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lọc order item theo trạng thái, thanh toán và khiếu nại.</p>
                </div>

                <button type="button" x-on:click="filtersOpen = ! filtersOpen" x-bind:aria-expanded="filtersOpen.toString()" aria-controls="seller-orders-filters" class="inline-flex items-center gap-2 rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                    <i class="fa-solid fa-sliders"></i>
                    <span x-text="filtersOpen ? 'Ẩn bộ lọc' : 'Hiện bộ lọc'"></span>
                </button>
            </div>

            <div id="seller-orders-filters" x-show="filtersOpen" x-transition.opacity.duration.200ms style="display: none;" class="mt-5 border-t border-black/8 pt-5 dark:border-white/10">
                <div class="grid gap-4">
                <label class="block">
                    <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Tìm kiếm</span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Mã đơn, mã item, buyer, sản phẩm" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-[#D32F2F]/35 focus:ring-2 focus:ring-[#D32F2F]/10 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200">
                </label>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Trạng thái item</span>
                        <select wire:model.live="status" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-[#D32F2F]/35 focus:ring-2 focus:ring-[#D32F2F]/10 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200">
                            <option value="all">Tất cả</option>
                            @foreach(\App\Enums\OrderStatus::cases() as $statusCase)
                                <option value="{{ $statusCase->value }}">{{ $statusCase->label() }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Payment status</span>
                        <select wire:model.live="paymentStatus" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-[#D32F2F]/35 focus:ring-2 focus:ring-[#D32F2F]/10 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200">
                            <option value="all">Tất cả</option>
                            @foreach(\App\Enums\PaymentStatus::cases() as $paymentCase)
                                <option value="{{ $paymentCase->value }}">{{ $paymentCase->label() }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Khiếu nại</span>
                        <select wire:model.live="complaintFilter" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-[#D32F2F]/35 focus:ring-2 focus:ring-[#D32F2F]/10 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200">
                            <option value="all">Tất cả</option>
                            <option value="with">Có khiếu nại</option>
                            <option value="without">Không có khiếu nại</option>
                            <option value="open">Đang mở</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Sắp xếp</span>
                        <select wire:model.live="sortBy" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-[#D32F2F]/35 focus:ring-2 focus:ring-[#D32F2F]/10 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200">
                            <option value="latest">Mới nhất</option>
                            <option value="oldest">Cũ nhất</option>
                            <option value="revenue_high">Doanh thu cao</option>
                            <option value="revenue_low">Doanh thu thấp</option>
                        </select>
                    </label>
                </div>

                <label class="block">
                    <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Khoảng thời gian</span>
                    <select wire:model.live="timeFilter" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-[#D32F2F]/35 focus:ring-2 focus:ring-[#D32F2F]/10 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200">
                        <option value="last_30_days">30 ngày gần nhất</option>
                        <option value="today">Hôm nay</option>
                        <option value="last_7_days">7 ngày gần nhất</option>
                        <option value="month_to_date">Tháng này</option>
                        <option value="year_to_date">Năm nay</option>
                        <option value="all_time">Toàn thời gian</option>
                    </select>
                </label>
            </div>
            </div>
        </article>
    </section>

    <section class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Danh sách order item</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mỗi dòng là một item thuộc seller, gắn với order và buyer tương ứng.</p>
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-black/8 dark:border-white/10">
            <div class="overflow-x-auto">
                <table class="min-w-[1120px] w-full border-separate border-spacing-0">
                    <thead class="bg-[#FCF9F4] text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:bg-white/5 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="border-b border-black/8 px-4 py-3 text-left dark:border-white/10">Order</th>
                            <th scope="col" class="border-b border-black/8 px-4 py-3 text-left dark:border-white/10">Sản phẩm</th>
                            <th scope="col" class="border-b border-black/8 px-4 py-3 text-right dark:border-white/10">Qty</th>
                            <th scope="col" class="border-b border-black/8 px-4 py-3 text-right dark:border-white/10">Seller nhận</th>
                            <th scope="col" class="border-b border-black/8 px-4 py-3 text-right dark:border-white/10">Phí</th>
                            <th scope="col" class="border-b border-black/8 px-4 py-3 text-center dark:border-white/10">Item</th>
                            <th scope="col" class="border-b border-black/8 px-4 py-3 text-right dark:border-white/10">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-black/8 dark:divide-white/10">
                        @forelse($items as $item)
                            <tr class="align-top">
                                <td class="px-4 py-4">
                                    <div class="space-y-1">
                                        <p class="text-sm font-semibold text-gray-950 dark:text-white">#{{ $item['order_code'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['order_item_code'] ?? ('#'.$item['id']) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['buyer_username'] ?? '-' }}</p>
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="space-y-1">
                                        <p class="font-semibold text-gray-950 dark:text-white">{{ $item['product_name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['variant_summary'] ?: 'Không có variant' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['buyer_email'] ?? '-' }}</p>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="inline-flex min-w-16 justify-center rounded-2xl bg-slate-50 px-3 py-2 text-sm dark:bg-white/5">
                                        <span class="font-semibold text-gray-950 dark:text-white">{{ number_format($item['quantity']) }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="inline-flex min-w-32 justify-center rounded-2xl bg-slate-50 px-3 py-2 text-sm dark:bg-white/5">
                                        <span class="font-semibold text-gray-950 dark:text-white">{{ number_format($item['seller_amount'], 0, ',', '.') }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="inline-flex min-w-28 justify-center rounded-2xl bg-slate-50 px-3 py-2 text-sm dark:bg-white/5">
                                        <span class="font-semibold text-gray-950 dark:text-white">{{ number_format($item['platform_fee'], 0, ',', '.') }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-center">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ match ($item['status']) {
                                            \App\Enums\OrderStatus::PendingPayment => 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-300',
                                            \App\Enums\OrderStatus::Processing => 'bg-blue-500/10 text-blue-700 dark:text-blue-300',
                                            \App\Enums\OrderStatus::Delivered => 'bg-purple-500/10 text-purple-700 dark:text-purple-300',
                                            \App\Enums\OrderStatus::Disputing => 'bg-orange-500/10 text-orange-700 dark:text-orange-300',
                                            \App\Enums\OrderStatus::Completed => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                                            \App\Enums\OrderStatus::Cancelled => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                                            \App\Enums\OrderStatus::Refunded => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
                                            default => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
                                        } }}">{{ $item['status_label'] }}</span>
                                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ match ($item['escrow_status']) {
                                            \App\Enums\EscrowStatus::Holding => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                                            \App\Enums\EscrowStatus::Released => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                                            \App\Enums\EscrowStatus::Refunded => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                                            default => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
                                        } }}">{{ $item['escrow_status']?->label() ?? 'No escrow' }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        @if($item['complaint'] !== null)
                                            <a href="{{ route('seller.complaints.show', ['complaint' => $item['complaint_code'] ?? $item['complaint']->id]) }}" class="rounded-full bg-black px-3 py-1 text-[11px] font-semibold text-white dark:bg-white dark:text-gray-950">Có khiếu nại</a>
                                        @endif
                                        <button type="button" wire:click="openDetailModal({{ $item['id'] }})" class="rounded-full border border-black/10 px-3 py-1 text-[11px] font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                                            Xem chi tiết
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-sm text-gray-500 dark:text-gray-400">Chưa có order item nào trong khoảng thời gian này.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $items->links() }}
        </div>
    </section>

    <x-reusable.modal wire:model="showDetailModal" title="Chi tiết order item" max-width="4xl">
        @if($detailItem)
            <div class="space-y-4 text-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-black/8 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Order</p>
                        <p class="mt-2 font-semibold text-gray-950 dark:text-white">#{{ $detailItem->order?->order_code }}</p>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $detailItem->order?->buyer?->username ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl border border-black/8 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Trạng thái</p>
                        <p class="mt-2 font-semibold text-gray-950 dark:text-white">{{ $detailItem->status->label() }}</p>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $detailItem->order?->payment_status?->label() ?? '-' }}</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-black/8 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Sản phẩm</p>
                    <p class="mt-2 font-semibold text-gray-950 dark:text-white">{{ $detailItem->product_name_snapshot }}</p>
                    <p class="mt-1 text-gray-600 dark:text-gray-300">{{ collect([$detailItem->listing?->variant?->region?->name, $detailItem->listing?->variant?->platform?->name, $detailItem->listing?->variant?->operatingSystem?->name, $detailItem->listing?->variant?->edition])->filter()->implode(' · ') }}</p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-black/8 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Subtotal</p>
                        <p class="mt-2 font-semibold text-gray-950 dark:text-white">{{ number_format((float) $detailItem->subtotal, 0, ',', '.') }} VND</p>
                    </div>
                    <div class="rounded-2xl border border-black/8 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Phí nền tảng</p>
                        <p class="mt-2 font-semibold text-gray-950 dark:text-white">{{ number_format((float) $detailItem->platform_fee, 0, ',', '.') }} VND</p>
                    </div>
                    <div class="rounded-2xl border border-black/8 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Seller nhận</p>
                        <p class="mt-2 font-semibold text-gray-950 dark:text-white">{{ number_format((float) $detailItem->seller_amount, 0, ',', '.') }} VND</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-black/8 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Escrow</p>
                        <p class="mt-2 font-semibold text-gray-950 dark:text-white">{{ $detailItem->escrow?->status?->label() ?? 'Không có' }}</p>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $detailItem->escrow?->amount ? number_format((float) $detailItem->escrow->amount, 0, ',', '.') . ' VND' : '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-black/8 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Khiếu nại</p>
                        <p class="mt-2 font-semibold text-gray-950 dark:text-white">{{ $detailItem->complaint?->status?->label() ?? 'Không có' }}</p>
                        @if($detailItem->complaint !== null)
                            <a href="{{ route('seller.complaints.show', ['complaint' => $detailItem->complaint->complaint_code ?? $detailItem->complaint->id]) }}" class="mt-1 inline-flex text-[#D32F2F] hover:underline">Mở hội thoại khiếu nại</a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <x-slot:footer>
            <button type="button" wire:click="closeDetailModal" class="rounded-lg border border-black/10 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-white/5">
                Đóng
            </button>
        </x-slot:footer>
    </x-reusable.modal>

</div>
