@php
    $sellerDashboardUrl = \Illuminate\Support\Facades\Route::has('seller.dashboard.index')
        ? route('seller.dashboard.index')
        : route('seller.apply');
@endphp

<div class="relative overflow-hidden px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-80 bg-gradient-to-b from-[#F6EBD9] via-[#FCF9F4] to-transparent dark:from-gray-900 dark:via-gray-950"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -top-10 right-0 -z-10 h-64 w-64 rounded-full bg-[#D32F2F]/8 blur-3xl dark:bg-[#D32F2F]/10"></div>
    <div aria-hidden="true" class="pointer-events-none absolute left-0 top-24 -z-10 h-64 w-64 rounded-full bg-indigo-500/8 blur-3xl dark:bg-indigo-400/10"></div>

    <div class="mx-auto max-w-7xl space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-5 shadow-[0_24px_80px_-40px_rgba(0,0,0,0.35)] sm:p-6 lg:p-8 dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
            <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 hidden w-1/2 bg-radial from-white/80 via-white/10 to-transparent lg:block dark:from-white/10 dark:via-white/5"></div>

            <div class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end">
                <div class="space-y-4">
                    <nav aria-label="Breadcrumb">
                        <ol class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <li>
                                <a href="{{ route('app.shop.index') }}" class="transition-colors hover:text-[#D32F2F] dark:hover:text-[#ff8b8b]">
                                    <i class="fa-solid fa-house"></i>
                                </a>
                            </li>
                            <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
                            <li class="font-semibold text-gray-800 dark:text-gray-200" aria-current="page">Thư viện của tôi</li>
                        </ol>
                    </nav>

                    <div class="space-y-3">
                        <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] shadow-sm dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#D32F2F]"></span>
                            Khu vực người mua
                        </p>
                        <div>
                             <h1 class="text-3xl font-semibold tracking-tight text-gray-950 sm:text-4xl dark:text-white">Thư viện của tôi</h1>
                             <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 sm:text-base dark:text-gray-400">Quản lý đơn chờ thanh toán, giao dịch hoàn tất, key đã mở và khiếu nại tại một nơi.</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                         <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Đơn hàng</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $orders->count() }}</p>
                    </div>
                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                         <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Chờ thanh toán</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $pendingPaymentCount }}</p>
                    </div>
                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                         <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Hoàn tất</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $completedOrderCount }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[1.75rem] border border-black/8 bg-white/90 px-5 py-5 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1">
                     <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Đăng ký người bán</p>
                     <h2 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $hasApprovedSellerAccount ? 'Bảng điều khiển người bán' : 'Trở thành người bán' }}</h2>
                    <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">
                        @if($hasApprovedSellerAccount)
                             Bạn đã có quyền người bán. Mở dashboard để quản lý listing và key.
                        @else
                             {{ $user->hasVerifiedEmail() ? 'Mở đơn đăng ký người bán và hoàn tất thông tin KYC.' : 'Vui lòng xác minh email trước để đăng ký người bán.' }}
                        @endif
                    </p>
                </div>

                <div class="space-y-2 sm:text-right">
                    @if($hasApprovedSellerAccount)
                        <a href="{{ $sellerDashboardUrl }}" class="inline-flex items-center justify-center rounded-2xl bg-black px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                            Mở bảng điều khiển
                        </a>
                    @elseif($user->hasVerifiedEmail())
                        <a href="{{ route('seller.apply') }}" class="inline-flex items-center justify-center rounded-2xl bg-black px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                             Trở thành người bán
                        </a>
                    @else
                        <span aria-disabled="true" class="inline-flex cursor-not-allowed items-center justify-center rounded-2xl bg-gray-400/20 px-5 py-3 text-sm font-semibold text-gray-500 dark:bg-white/10 dark:text-gray-400">
                             Trở thành người bán
                        </span>
                         <p class="text-xs text-amber-700 dark:text-amber-300">Bạn cần xác minh email để tiếp tục.</p>
                    @endif
                </div>
            </div>
        </section>

        @if(session('library-status'))
            <div class="rounded-[1.75rem] border border-emerald-500/15 bg-emerald-500/8 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                {{ session('library-status') }}
            </div>
        @endif

        <div class="space-y-4">
            @forelse($orders as $order)
                @php
                    $orderStatusClasses = match ($order->status->value) {
                        0, 1 => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                        2, 4 => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                        3, 5, 6 => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                        default => 'bg-gray-500/10 text-gray-700 dark:text-gray-300',
                    };

                    $paymentStatusClasses = match ($order->payment_status?->value) {
                        1 => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                        0 => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                        default => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                    };

                    $isPendingPayment = $order->payment_status === \App\Enums\PaymentStatus::Pending;
                    $isPaidOrder = $order->payment_status === \App\Enums\PaymentStatus::Completed;
                @endphp

                <article class="rounded-[2rem] border border-black/8 bg-white/90 p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-black px-3 py-1 text-[11px] font-semibold text-white dark:bg-white dark:text-gray-950">{{ $order->order_code }}</span>
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $orderStatusClasses }}">{{ $order->status->label() }}</span>
                                 <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $paymentStatusClasses }}">{{ $order->payment_status?->label() ?? 'Không xác định' }}</span>
                            </div>

                            <div>
                                 <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Đơn hàng {{ $order->payment_method?->label() ?? 'thanh toán' }}</h2>
                                 <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Đặt lúc {{ $order->created_at?->format('d/m/Y H:i') ?? '--' }} với {{ $order->items_count }} sản phẩm.</p>
                            </div>

                            @if($isPendingPayment)
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" wire:click="continuePayment({{ $order->id }})" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                         Tiếp tục thanh toán
                                    </button>
                                    <button type="button" wire:click="cancelOrder({{ $order->id }})" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-rose-500/30 hover:text-rose-600 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-rose-400/30 dark:hover:text-rose-300">
                                         Hủy đơn hàng
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-3 text-left shadow-sm dark:border-white/10 dark:bg-gray-800/70 lg:min-w-44 lg:text-right">
                             <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Tổng đơn hàng</div>
                            <div class="mt-2 text-lg font-semibold text-gray-950 dark:text-white">{{ number_format((float) $order->total_price, 0, ',', '.') }} VND</div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4 border-t border-black/8 pt-6 dark:border-white/10">
                        @foreach($order->items as $item)
                            @php
                                $variant = $item->listing?->variant;
                                $product = $variant?->product;

                                $itemStatusClasses = match ($item->status?->value) {
                                    0, 1 => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                                    2, 4 => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                                    3, 5, 6 => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                                    default => 'bg-gray-500/10 text-gray-700 dark:text-gray-300',
                                };

                                $complaintStatusClasses = $item->complaint ? match ($item->complaint->status->value) {
                                    0 => 'bg-blue-500/10 text-blue-700 dark:text-blue-300',
                                    1 => 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-300',
                                    2 => 'bg-orange-500/10 text-orange-700 dark:text-orange-300',
                                    3 => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                                    4 => 'bg-gray-500/10 text-gray-700 dark:text-gray-300',
                                    default => 'bg-gray-500/10 text-gray-700 dark:text-gray-300',
                                } : null;

                                $keyState = $revealedKeys[$item->id] ?? null;
                                $visibleKeys = $keyState['keys'] ?? [];
                                $isKeyVisible = $keyState['visible'] ?? false;
                                $hasViewedKey = $item->buyer_key_viewed_at !== null;
                                $canViewKeys = in_array($item->status, [
                                    \App\Enums\OrderStatus::Delivered,
                                    \App\Enums\OrderStatus::Disputing,
                                    \App\Enums\OrderStatus::Completed,
                                ], true);

                                $viewKeyButtonClasses = $hasViewedKey
                                    ? 'inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/30 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/30 dark:hover:text-[#ff9c9c]'
                                    : 'inline-flex items-center justify-center rounded-2xl border border-amber-500/20 bg-amber-500/10 px-4 py-2.5 text-sm font-semibold text-amber-800 shadow-sm ring-1 ring-amber-400/20 transition-colors hover:border-amber-500/30 hover:bg-amber-500/15 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200 dark:ring-amber-300/20 dark:hover:bg-amber-400/15';
                            @endphp

                            <div class="rounded-[1.6rem] border border-black/8 bg-[#FCF9F4] p-4 dark:border-white/10 dark:bg-white/5">
                                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                    <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                 <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $itemStatusClasses }}">{{ $item->status?->label() ?? 'Không xác định' }}</span>
                                 <span class="rounded-full border border-black/8 bg-white px-3 py-1 text-[11px] font-semibold text-gray-700 dark:border-white/10 dark:bg-gray-950 dark:text-gray-300">SL {{ $item->quantity }}</span>
                                 <span class="rounded-full border border-black/8 bg-white px-3 py-1 text-[11px] font-semibold text-gray-700 dark:border-white/10 dark:bg-gray-950 dark:text-gray-300">{{ $item->keys_count }} key</span>
                                <span class="rounded-full border border-black/8 bg-white px-3 py-1 text-[11px] font-semibold text-gray-700 dark:border-white/10 dark:bg-gray-950 dark:text-gray-300">{{ $item->order_item_code ?? ('#'.$item->id) }}</span>
                                @if($item->complaint)
                                    <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $complaintStatusClasses }}">{{ $item->complaint->status->label() }}</span>
                                @endif
                                        </div>

                                        <div>
                                            <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $item->product_name_snapshot }}</h3>
                                             <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $product?->name ?? 'Sản phẩm' }}</p>
                                            <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">
                                                 <span class="rounded-full border border-black/8 bg-white px-3 py-1 dark:border-white/10 dark:bg-gray-950">{{ $variant?->edition ?? 'Bản tiêu chuẩn' }}</span>
                                                 <span class="rounded-full border border-black/8 bg-white px-3 py-1 dark:border-white/10 dark:bg-gray-950">{{ $variant?->region?->name ?? $variant?->region?->slug ?? 'Khu vực' }}</span>
                                                 <span class="rounded-full border border-black/8 bg-white px-3 py-1 dark:border-white/10 dark:bg-gray-950">{{ $variant?->platform?->name ?? $variant?->platform?->slug ?? 'Nền tảng' }}</span>
                                                <span class="rounded-full border border-black/8 bg-white px-3 py-1 dark:border-white/10 dark:bg-gray-950">{{ $variant?->operatingSystem?->name ?? $variant?->operatingSystem?->slug ?? 'OS' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-start gap-3 xl:items-end">
                                        <div class="text-left xl:text-right">
                                             <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Tạm tính sản phẩm</div>
                                            <div class="mt-1 text-base font-semibold text-gray-950 dark:text-white">{{ number_format((float) $item->subtotal, 0, ',', '.') }} VND</div>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" wire:click="openItemDetails({{ $item->id }})" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/30 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/30 dark:hover:text-[#ff9c9c]">
                                                 Xem chi tiết
                                            </button>

                                            @if($item->keys_count > 0 && $canViewKeys)
                                                @if($hasViewedKey)
                                                    <button type="button" wire:click="toggleOrderItemKeys({{ $item->id }})" class="{{ $viewKeyButtonClasses }}">
                                                         {{ $isKeyVisible ? 'Ẩn key' : 'Xem key' }}
                                                    </button>
                                                @else
                                                    <button type="button" wire:click="promptKeyReveal({{ $item->id }})" class="{{ $viewKeyButtonClasses }}">
                                                         Xem key
                                                    </button>
                                                @endif
                                            @endif

                                            @if($isPaidOrder && $item->buyer_key_viewed_at !== null && in_array($item->status, [\App\Enums\OrderStatus::Delivered, \App\Enums\OrderStatus::Disputing], true))
                                                <button type="button" wire:click="openConfirmReceivedModal({{ $item->id }})" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-emerald-500/30 hover:text-emerald-600 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-emerald-400/30 dark:hover:text-emerald-300">
                                                     Xác nhận đã nhận
                                                </button>
                                            @endif

                                            @if($isPaidOrder && $item->buyer_key_viewed_at !== null)
                                                @if($item->complaint)
                                                    @php
                                                        $complaintRouteValue = $item->complaint->complaint_code ?: $item->complaint->id;
                                                        $complaintThreadUrl = route('app.library.complaints.show', ['complaint' => $complaintRouteValue]);
                                                    @endphp
                                                    <button type="button" wire:click="openComplaintDetails({{ $item->id }})" class="inline-flex items-center justify-center rounded-2xl border border-amber-500/20 bg-amber-500/10 px-4 py-2.5 text-sm font-semibold text-amber-800 shadow-sm ring-1 ring-amber-400/20 transition-colors hover:border-amber-500/30 hover:bg-amber-500/15 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200 dark:ring-amber-300/20 dark:hover:bg-amber-400/15">
                                                         Xem khiếu nại
                                                    </button>
                                                    <a href="{{ $complaintThreadUrl }}" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/30 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/30 dark:hover:text-[#ff9c9c]">
                                                         Mở trang khiếu nại
                                                    </a>
                                                @elseif($item->status !== \App\Enums\OrderStatus::Completed)
                                                    <button type="button" wire:click="openComplaintForm({{ $item->id }})" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-rose-500/30 hover:text-rose-600 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-rose-400/30 dark:hover:text-rose-300">
                                                         Mở khiếu nại
                                                    </button>
                                                @endif
                                            @endif

                                            @if($item->status === \App\Enums\OrderStatus::Completed)
                                                @if($item->review)
                                                    <span class="inline-flex items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                                                         Đã đánh giá
                                                    </span>
                                                @else
                                                    <button type="button" wire:click="openReviewForm({{ $item->id }})" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-emerald-500/30 hover:text-emerald-600 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-emerald-400/30 dark:hover:text-emerald-300">
                                                         Viết đánh giá
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($visibleKeys !== [] && ($hasViewedKey || $isKeyVisible))
                                    <div class="mt-4 space-y-3 border-t border-black/8 pt-4 dark:border-white/10">
                                        <div class="rounded-2xl border border-amber-500/15 bg-amber-500/8 px-4 py-3 text-sm text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                                             Hãy quay toàn màn hình từ lúc mở key đến khi thử kích hoạt. Tải video đó lên nếu key giả, hết hạn hoặc không hợp lệ.
                                        </div>

                                        <div class="space-y-2">
                                            @foreach($visibleKeys as $keyCode)
                                                <div class="overflow-x-auto rounded-2xl bg-gray-950 px-4 py-3 font-mono text-sm text-white">{{ $isKeyVisible ? $keyCode : '****' }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="rounded-[2rem] border border-dashed border-black/15 bg-white/90 p-10 text-center shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#FCF9F4] text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <i class="fa-solid fa-gamepad"></i>
                    </div>
                     <h2 class="mt-5 text-lg font-semibold text-gray-950 dark:text-white">Thư viện của bạn đang trống</h2>
                     <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Các đơn đã mua và đơn chờ thanh toán sẽ xuất hiện tại đây.</p>
                </div>
            @endforelse
        </div>

        @if($selectedOrderItem)
            @php
                $selectedVariant = $selectedOrderItem->listing?->variant;
                $selectedProduct = $selectedVariant?->product;
            @endphp

            <div class="fixed inset-0 z-[80] flex items-center justify-center p-4">
                <button type="button" wire:click="closeItemDetails" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></button>

                <div class="relative z-10 w-full max-w-2xl rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#D32F2F] dark:text-[#ff9c9c]">Chi tiết biến thể</p>
                            <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">{{ $selectedOrderItem->product_name_snapshot }}</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Thông tin tham chiếu nhanh cho đúng listing và biến thể bạn đã mua.</p>
                        </div>

                        <button type="button" wire:click="closeItemDetails" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-black transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-950 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Sản phẩm</p>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedProduct?->name ?? 'Sản phẩm cửa hàng' }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Listing</p>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedOrderItem->listing?->display_name ?: ($selectedProduct?->name ?? 'Listing chưa có tên') }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Phiên bản</p>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedVariant?->edition ?? 'Standard' }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Mã đơn hàng</p>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedOrderItem->order_item_code ?? ('#'.$selectedOrderItem->id) }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Khu vực</p>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedVariant?->region?->name ?? $selectedVariant?->region?->slug ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Nền tảng / HĐH</p>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedVariant?->platform?->name ?? $selectedVariant?->platform?->slug ?? '-' }} / {{ $selectedVariant?->operatingSystem?->name ?? $selectedVariant?->operatingSystem?->slug ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-black/8 bg-white px-4 py-4 dark:border-white/10 dark:bg-gray-950">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Số lượng</p>
                            <p class="mt-2 text-lg font-semibold text-gray-950 dark:text-white">{{ $selectedOrderItem->quantity }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/8 bg-white px-4 py-4 dark:border-white/10 dark:bg-gray-950">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Đơn giá</p>
                            <p class="mt-2 text-lg font-semibold text-gray-950 dark:text-white">{{ number_format((float) $selectedOrderItem->unit_price, 0, ',', '.') }} VND</p>
                        </div>
                        <div class="rounded-2xl border border-black/8 bg-white px-4 py-4 dark:border-white/10 dark:bg-gray-950">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Tạm tính</p>
                            <p class="mt-2 text-lg font-semibold text-gray-950 dark:text-white">{{ number_format((float) $selectedOrderItem->subtotal, 0, ',', '.') }} VND</p>
                        </div>
                    </div>

                    @if($selectedOrderItem->review)
                        <div class="mt-6 rounded-2xl border border-emerald-500/15 bg-emerald-500/8 p-4 dark:border-emerald-400/20 dark:bg-emerald-400/10">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">Đánh giá của bạn</p>
                                    <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedOrderItem->review->rating }}/5</p>
                                </div>
                                <div class="flex items-center gap-1 text-amber-400">
                                    @for($star = 1; $star <= (int) $selectedOrderItem->review->rating; $star++)
                                        <i class="fa-solid fa-star"></i>
                                    @endfor
                                </div>
                            </div>

                            @if(filled($selectedOrderItem->review->comment))
                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-emerald-800 dark:text-emerald-100">{{ $selectedOrderItem->review->comment }}</p>
                            @endif

                            @if(($selectedReviewMedia ?? []) !== [])
                                <div x-data="{ previewUrl: null }" class="mt-4 space-y-2 border-t border-emerald-500/15 pt-4 dark:border-emerald-400/20">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">Tệp đính kèm</p>
                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        @foreach($selectedReviewMedia as $media)
                                            @php
                                                $extension = strtolower(pathinfo($media['label'], PATHINFO_EXTENSION));
                                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true);
                                                $isPdf = $extension === 'pdf';
                                            @endphp

                                            @if($media['url'] && $isImage)
                                                <button type="button" x-on:click="previewUrl = @js($media['url'])" class="group overflow-hidden rounded-xl border border-emerald-500/15 bg-white shadow-sm transition hover:border-emerald-500/30 hover:shadow-md dark:border-emerald-400/20 dark:bg-gray-950">
                                                    <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-900">
                                                        <img src="{{ $media['url'] }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                                                    </div>
                                                </button>
                                            @elseif($media['url'] && $isPdf)
                                                <a href="{{ $media['url'] }}" target="_blank" rel="noopener noreferrer" class="overflow-hidden rounded-xl border border-emerald-500/15 bg-white shadow-sm transition hover:border-emerald-500/30 hover:shadow-md dark:border-emerald-400/20 dark:bg-gray-950">
                                                    <div class="flex items-center justify-center border-b border-emerald-500/15 bg-emerald-500/8 px-3 py-3 dark:border-emerald-400/20 dark:bg-emerald-400/10">
                                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 text-red-600 dark:text-red-300">
                                                            <i class="fa-solid fa-file-pdf"></i>
                                                        </div>
                                                    </div>
                                                    <object data="{{ $media['url'] }}" type="application/pdf" class="h-64 w-full">
                                                        <div class="p-3 text-sm text-gray-500 dark:text-gray-400">PDF preview unavailable.</div>
                                                    </object>
                                                </a>
                                            @elseif($media['url'])
                                                <a href="{{ $media['url'] }}" target="_blank" rel="noopener noreferrer" class="block rounded-xl border border-emerald-500/15 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm hover:border-emerald-500/30 hover:text-emerald-600 dark:border-emerald-400/20 dark:bg-gray-950 dark:text-gray-300 dark:hover:border-emerald-400/30 dark:hover:text-emerald-300">
                                                    {{ $media['label'] }}
                                                </a>
                                            @else
                                                <div class="rounded-xl border border-emerald-500/15 bg-white px-3 py-2 text-sm text-gray-700 dark:border-emerald-400/20 dark:bg-gray-950 dark:text-gray-300">
                                                    {{ $media['label'] }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <div
                                        x-cloak
                                        x-show="previewUrl"
                                        x-transition.opacity
                                        x-on:click.self="previewUrl = null"
                                        class="fixed inset-0 z-[90] flex items-center justify-center bg-black/75 p-4"
                                    >
                                        <button type="button" x-on:click="previewUrl = null" class="absolute inset-0 cursor-default" aria-label="Đóng xem trước"></button>
                                        <div class="relative z-10 max-h-[90vh] max-w-[92vw] overflow-hidden rounded-2xl bg-black shadow-2xl">
                                            <img :src="previewUrl" alt="" class="max-h-[90vh] max-w-[92vw] object-contain">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($selectedComplaintOrderItem && $selectedComplaint)
            @php
                $selectedComplaintRouteValue = $selectedComplaint->complaint_code ?: $selectedComplaint->id;
                $selectedComplaintThreadUrl = route('app.library.complaints.show', ['complaint' => $selectedComplaintRouteValue]);

                $selectedComplaintStatusClasses = match ($selectedComplaint->status->value) {
                    0 => 'bg-blue-500/10 text-blue-700 dark:text-blue-300',
                    1 => 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-300',
                    2 => 'bg-orange-500/10 text-orange-700 dark:text-orange-300',
                    3 => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                    4 => 'bg-gray-500/10 text-gray-700 dark:text-gray-300',
                    default => 'bg-gray-500/10 text-gray-700 dark:text-gray-300',
                };

                $canReplyToComplaint = in_array($selectedComplaint->status, [
                    \App\Enums\ComplaintStatus::Open,
                    \App\Enums\ComplaintStatus::InProcess,
                    \App\Enums\ComplaintStatus::Escalated,
                ], true);
            @endphp

            <div class="fixed inset-0 z-[80] flex items-center justify-center p-4">
                <button type="button" wire:click="closeComplaintDetails" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></button>

                <div class="relative z-10 w-full max-w-4xl rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#D32F2F] dark:text-[#ff9c9c]">Chi tiết khiếu nại</p>
                            <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">{{ $selectedComplaint->complaint_code ?? ('Khiếu nại #'.$selectedComplaint->id) }}</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Theo dõi toàn bộ hội thoại khiếu nại và bổ sung thông tin nếu vụ việc còn đang xử lý.</p>
                        </div>

                        <button type="button" wire:click="closeComplaintDetails" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-black transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-950 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Trạng thái</p>
                            <span class="mt-2 inline-flex rounded-full px-3 py-1 text-[11px] font-semibold {{ $selectedComplaintStatusClasses }}">{{ $selectedComplaint->status->label() }}</span>
                        </div>
                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Mã đơn hàng</p>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedComplaintOrder?->order_code ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Tin nhắn</p>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ count($selectedComplaintMessages ?? []) }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Mở lúc</p>
                            <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedComplaint->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] p-4 dark:border-white/10 dark:bg-white/5">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Lý do của người mua</p>
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-gray-300">{{ $selectedComplaint->reason }}</p>
                            </div>

                            @if(($selectedComplaintEvidence ?? []) !== [])
                                <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] p-4 dark:border-white/10 dark:bg-white/5">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Bằng chứng</p>
                                    <div class="mt-3 space-y-2">
                                        @foreach($selectedComplaintEvidence as $evidence)
                                            @if($evidence['url'])
                                                <a href="{{ $evidence['url'] }}" target="_blank" rel="noopener noreferrer" class="block rounded-xl border border-black/8 bg-white px-3 py-2 text-sm text-gray-700 hover:border-[#D32F2F]/20 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-300">
                                                    {{ $evidence['label'] }}
                                                </a>
                                            @else
                                                <div class="rounded-xl border border-black/8 bg-white px-3 py-2 text-sm text-gray-700 dark:border-white/10 dark:bg-gray-950 dark:text-gray-300">
                                                    {{ $evidence['label'] }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="flex flex-wrap gap-3">
                                <a href="{{ $selectedComplaintThreadUrl }}" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                    Mở trang khiếu nại
                                </a>
                            </div>

                            @if($selectedComplaint->resolved_at || filled($selectedComplaint->resolution_note))
                                <div class="rounded-2xl border border-emerald-500/15 bg-emerald-500/8 p-4 text-sm text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                                    <p class="font-semibold">Ghi chú xử lý</p>
                                    <p class="mt-2 whitespace-pre-line leading-6">{{ $selectedComplaint->resolution_note ?? 'Đã xử lý.' }}</p>
                                    @if($selectedComplaint->resolvedBy)
                                        <p class="mt-3 text-xs uppercase tracking-[0.16em]">Xử lý bởi {{ $selectedComplaint->resolvedBy->username }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] p-4 dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Hội thoại</h3>
                                <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ count($selectedComplaintMessages ?? []) }} tin nhắn</span>
                            </div>

                            <div class="mt-4 space-y-3">
                                @forelse($selectedComplaintMessages ?? [] as $message)
                                    <div class="rounded-2xl border border-black/8 bg-white p-4 dark:border-white/10 dark:bg-gray-950">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $message['sender_name'] }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $message['created_at'] }}</p>
                                        </div>
                                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-gray-300">{{ $message['message'] }}</p>

                                        @if(($message['attachments'] ?? []) !== [])
                                            <div class="mt-3 space-y-2">
                                                @foreach($message['attachments'] as $attachment)
                                                    @if($attachment['url'])
                                                        <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer" class="block rounded-xl border border-black/8 bg-[#FCF9F4] px-3 py-2 text-sm text-gray-700 hover:border-[#D32F2F]/20 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-900 dark:text-gray-300">
                                                            {{ $attachment['label'] }}
                                                        </a>
                                                    @else
                                                        <div class="rounded-xl border border-black/8 bg-[#FCF9F4] px-3 py-2 text-sm text-gray-700 dark:border-white/10 dark:bg-gray-900 dark:text-gray-300">
                                                            {{ $attachment['label'] }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-black/10 bg-white p-4 text-sm text-gray-500 dark:border-white/10 dark:bg-gray-950 dark:text-gray-400">
                                        Chưa có tin nhắn.
                                    </div>
                                @endforelse
                            </div>

                            @if($canReplyToComplaint)
                                <form wire:submit="replyComplaint" class="mt-4 space-y-3 border-t border-black/8 pt-4 dark:border-white/10">
                                    <div>
                                        <label for="complaint-reply-message" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Thêm tin nhắn</label>
                                        <textarea id="complaint-reply-message" wire:model="complaintReplyMessage" rows="4" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100" placeholder="Bổ sung thông tin hoặc cập nhật mới..."></textarea>
                                        @error('complaintReplyMessage')
                                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="complaint-reply-attachments" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Tệp đính kèm</label>
                                        <input id="complaint-reply-attachments" wire:model="complaintReplyAttachments" type="file" multiple accept="image/*,video/*,application/pdf" class="mt-2 block w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-black file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white dark:border-white/10 dark:bg-gray-950 dark:text-gray-100 file:dark:bg-white file:dark:text-gray-950">
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Không bắt buộc. Tối đa 5 tệp.</p>
                                        @error('complaintReplyAttachments')
                                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                        @enderror
                                        @error('complaintReplyAttachments.*')
                                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex flex-wrap justify-end gap-3">
                                        <button type="button" wire:click="closeComplaintDetails" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                                            Đóng
                                        </button>
                                        <button type="submit" wire:loading.attr="disabled" wire:target="replyComplaint" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                            Gửi tin nhắn
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($selectedConfirmReceivedOrderItem)
            <div class="fixed inset-0 z-[80] flex items-center justify-center p-4">
                <button type="button" wire:click="cancelConfirmReceivedModal" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></button>

                <div class="relative z-10 w-full max-w-lg rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-300">Xác nhận đã nhận</p>
                            <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Đánh dấu đơn này là hoàn tất?</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Chỉ xác nhận khi bạn đã nhận và kiểm tra sản phẩm.</p>
                        </div>

                        <button type="button" wire:click="cancelConfirmReceivedModal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-black transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-950 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="mt-6 rounded-2xl border border-black/8 bg-[#FCF9F4] p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Sản phẩm</p>
                        <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedConfirmReceivedOrderItem->product_name_snapshot }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $selectedConfirmReceivedOrderItem->order_item_code ?? ('#'.$selectedConfirmReceivedOrderItem->id) }}</p>
                    </div>

                    <div class="mt-6 flex flex-wrap justify-end gap-3">
                        <button type="button" wire:click="cancelConfirmReceivedModal" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                            Hủy
                        </button>
                        <button type="button" wire:click="confirmReceived({{ $selectedConfirmReceivedOrderItem->id }})" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70">
                            Vâng, xác nhận đã nhận
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($reviewOrderItemId)
            <div class="fixed inset-0 z-[80] flex items-center justify-center p-4">
                <button type="button" wire:click="cancelReviewForm" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></button>

                <div class="relative z-10 w-full max-w-xl rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-300">Đánh giá</p>
                            <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Chia sẻ trải nghiệm của bạn</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Đánh giá của bạn giúp người mua khác hiểu rõ chất lượng giao dịch.</p>
                        </div>

                        <button type="button" wire:click="cancelReviewForm" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-black transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-950 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form wire:submit="submitReview" class="mt-6 space-y-4">
                        <div>
                            <label for="library-review-rating" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Điểm đánh giá</label>
                            <select id="library-review-rating" wire:model="reviewRating" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                                <option value="5">5 - Xuất sắc</option>
                                <option value="4">4 - Tốt</option>
                                <option value="3">3 - Trung bình</option>
                                <option value="2">2 - Kém</option>
                                <option value="1">1 - Tệ</option>
                            </select>
                            @error('reviewRating')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="library-review-comment" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Nhận xét</label>
                            <textarea id="library-review-comment" wire:model="reviewComment" rows="4" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100" placeholder="Chia sẻ điều bạn hài lòng hoặc cần cải thiện..."></textarea>
                            @error('reviewComment')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="library-review-media" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Tệp đính kèm</label>
                            <input id="library-review-media" wire:model="reviewMedia" type="file" multiple accept="image/*,application/pdf" class="mt-2 block w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-black file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white dark:border-white/10 dark:bg-gray-950 dark:text-gray-100 file:dark:bg-white file:dark:text-gray-950">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Không bắt buộc. Tối đa 5 tệp.</p>
                            @error('reviewMedia')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                            @error('reviewMedia.*')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap justify-end gap-3">
                            <button type="button" wire:click="cancelReviewForm" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-colors hover:border-emerald-500/25 hover:text-emerald-600 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-emerald-400/25 dark:hover:text-emerald-300">
                                Hủy
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="submitReview" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-gray-950 dark:hover:bg-emerald-500 dark:hover:text-white">
                                Gửi đánh giá
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if($keyAccessOrderItemId)
            <div class="fixed inset-0 z-[80] flex items-center justify-center p-4">
                <button type="button" wire:click="cancelKeyReveal" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></button>

                <div class="relative z-10 w-full max-w-lg rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#D32F2F] dark:text-[#ff9c9c]">Truy cập bảo vệ</p>
                            <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Xác nhận mật khẩu để xem key</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Trước khi mở key đã mua, hãy nhập lại mật khẩu tài khoản và đọc kỹ lưu ý bên dưới.</p>
                        </div>

                        <button type="button" wire:click="cancelKeyReveal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-black transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-950 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="mt-5 rounded-2xl border border-amber-500/15 bg-amber-500/8 px-4 py-4 text-sm leading-6 text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                        Hãy ở lại màn hình xem key và kích hoạt trong lúc sử dụng mã. Tránh gián đoạn giữa chừng để việc hỗ trợ và xác minh quyền lợi dễ dàng hơn nếu có sự cố.
                    </div>

                    <form wire:submit="revealOrderItemKeys" class="mt-5 space-y-4">
                        <div>
                            <label for="library-key-access-password" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Mật khẩu tài khoản</label>
                            <input id="library-key-access-password" wire:model="keyAccessPassword" type="password" autofocus class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                            @error('keyAccessPassword')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap justify-end gap-3">
                            <button type="button" wire:click="cancelKeyReveal" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                                Hủy
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="revealOrderItemKeys" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                Hiện key
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if($complaintOrderItemId)
            <div class="fixed inset-0 z-[80] flex items-center justify-center p-4">
                <button type="button" wire:click="cancelComplaintForm" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></button>

                <div class="relative z-10 w-full max-w-xl rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#D32F2F] dark:text-[#ff9c9c]">Khiếu nại</p>
                            <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Mô tả vấn đề</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Hãy quay toàn màn hình khi mở key và thử sử dụng, sau đó mô tả lỗi gặp phải.</p>
                        </div>

                        <button type="button" wire:click="cancelComplaintForm" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-black transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-950 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form wire:submit="submitComplaint" class="mt-5 space-y-4">
                        <div>
                            <label for="complaint-reason" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Lý do</label>
                            <textarea id="complaint-reason" wire:model="complaintReason" rows="5" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100" placeholder="Mô tả lỗi key, sai khu vực, không kích hoạt được hoặc sai thông tin..."></textarea>
                            @error('complaintReason')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="complaint-evidence" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Bằng chứng</label>
                            <input id="complaint-evidence" wire:model="complaintEvidence" type="file" multiple accept="image/*,video/*,application/pdf" class="mt-2 block w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-black file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white dark:border-white/10 dark:bg-gray-950 dark:text-gray-100 file:dark:bg-white file:dark:text-gray-950">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Tải lên video quay toàn màn hình hoặc bằng chứng hỗ trợ. Bắt buộc. Tối đa 3 tệp.</p>
                            @error('complaintEvidence')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                            @error('complaintEvidence.*')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap justify-end gap-3">
                            <button type="button" wire:click="cancelComplaintForm" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                                Hủy
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="submitComplaint" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                Gửi khiếu nại
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
