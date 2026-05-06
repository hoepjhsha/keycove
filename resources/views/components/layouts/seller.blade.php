@props([
    'user' => null,
    'seller' => null,
    'activeSection' => 'dashboard',
])

@php
    $portalUser = $user ?? auth()->user();
    $portalSeller = $seller;
    $dashboardUrl = \Illuminate\Support\Facades\Route::has('seller.dashboard.index')
        ? route('seller.dashboard.index')
        : url('/seller/apply');

    $applicationUrl = \Illuminate\Support\Facades\Route::has('seller.apply')
        ? route('seller.apply')
        : url('/seller/apply');

    $listingsUrl = \Illuminate\Support\Facades\Route::has('seller.listings.index')
        ? route('seller.listings.index')
        : url('/seller/listings');

    $productsUrl = \Illuminate\Support\Facades\Route::has('seller.products.index')
        ? route('seller.products.index')
        : url('/seller/products');

    $ordersUrl = \Illuminate\Support\Facades\Route::has('seller.orders.index')
        ? route('seller.orders.index')
        : url('/seller/orders');

    $complaintsUrl = \Illuminate\Support\Facades\Route::has('seller.complaints.index')
        ? route('seller.complaints.index')
        : url('/seller/complaints');

    $withdrawalsUrl = \Illuminate\Support\Facades\Route::has('seller.withdrawals.index')
        ? route('seller.withdrawals.index')
        : url('/seller/withdrawals');

    $shopHomeUrl = \Illuminate\Support\Facades\Route::has('app.shop.index')
        ? route('app.shop.index')
        : url('/');

    $logoutUrl = \Illuminate\Support\Facades\Route::has('app.auth.logout')
        ? route('app.auth.logout')
        : url('/auth/login');

    $navItems = [
        ['label' => 'Tổng quan', 'url' => $dashboardUrl, 'section' => 'dashboard', 'icon' => 'fa-solid fa-chart-line'],
        ['label' => 'Hồ sơ đăng ký', 'url' => $applicationUrl, 'section' => 'application', 'icon' => 'fa-regular fa-id-card'],
        ['label' => 'Sản phẩm', 'url' => $productsUrl, 'section' => 'products', 'icon' => 'fa-solid fa-boxes-stacked'],
        ['label' => 'Listing', 'url' => $listingsUrl, 'section' => 'listings', 'icon' => 'fa-solid fa-tags'],
        ['label' => 'Đơn hàng', 'url' => $ordersUrl, 'section' => 'orders', 'icon' => 'fa-solid fa-bag-shopping'],
        ['label' => 'Khiếu nại', 'url' => $complaintsUrl, 'section' => 'complaints', 'icon' => 'fa-regular fa-comment-dots'],
        ['label' => 'Rút tiền', 'url' => $withdrawalsUrl, 'section' => 'withdrawals', 'icon' => 'fa-solid fa-money-bill-transfer'],
        ['label' => 'Về cửa hàng', 'url' => $shopHomeUrl, 'section' => 'shop', 'icon' => 'fa-solid fa-house'],
    ];

    $managementItems = [
        ['label' => 'Sản phẩm', 'url' => $productsUrl, 'section' => 'products', 'icon' => 'fa-solid fa-boxes-stacked'],
        ['label' => 'Listing', 'url' => $listingsUrl, 'section' => 'listings', 'icon' => 'fa-solid fa-tags'],
        ['label' => 'Đơn hàng', 'url' => $ordersUrl, 'section' => 'orders', 'icon' => 'fa-solid fa-bag-shopping'],
        ['label' => 'Khiếu nại', 'url' => $complaintsUrl, 'section' => 'complaints', 'icon' => 'fa-regular fa-comment-dots'],
        ['label' => 'Rút tiền', 'url' => $withdrawalsUrl, 'section' => 'withdrawals', 'icon' => 'fa-solid fa-money-bill-transfer'],
    ];

    $accountItems = [
        ['label' => 'Hồ sơ đăng ký', 'url' => $applicationUrl, 'section' => 'application', 'icon' => 'fa-regular fa-id-card'],
        ['label' => 'Về cửa hàng', 'url' => $shopHomeUrl, 'section' => 'shop', 'icon' => 'fa-solid fa-house'],
        ['label' => 'Đăng xuất', 'url' => $logoutUrl, 'section' => 'logout', 'icon' => 'fa-solid fa-right-from-bracket'],
    ];
@endphp

<x-layouts.app :title="$title ?? 'Kênh người bán'">
    @section('pre-app-name', 'Người bán')

    <div x-data="{ mobileMenuOpen: false }" class="min-h-screen bg-[#FCF9F4] text-gray-950 dark:bg-gray-950 dark:text-gray-100">
        <header class="sticky top-0 z-40 border-b border-black/8 bg-white/90 backdrop-blur dark:border-white/10 dark:bg-gray-900/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-black text-white shadow-sm dark:bg-white dark:text-gray-950">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] dark:text-[#ff9c9c]">Kênh người bán</p>
                        <h1 class="truncate text-lg font-semibold text-gray-950 dark:text-white">{{ $portalSeller?->shop_name ?: ($portalUser?->username ?? 'Kênh người bán') }}</h1>
                    </div>
                </div>

                <nav class="hidden items-center gap-2 text-sm font-semibold lg:flex lg:flex-nowrap">
                    <a href="{{ $dashboardUrl }}" class="inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2 transition-colors {{ $activeSection === 'dashboard' ? 'bg-black text-white dark:bg-white dark:text-gray-950' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10' }}">
                        <i class="fa-solid fa-chart-line text-[12px]"></i>
                        Tổng quan
                    </a>

                    <div class="relative shrink-0" x-data="{ open: false }">
                        <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()" class="inline-flex items-center gap-2 rounded-full px-4 py-2 transition-colors {{ in_array($activeSection, ['products', 'listings', 'orders', 'complaints', 'withdrawals'], true) ? 'bg-black text-white dark:bg-white dark:text-gray-950' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10' }}">
                            <i class="fa-solid fa-layer-group text-[12px]"></i>
                            Quản lý
                            <i class="fa-solid fa-chevron-down text-[10px] opacity-70 transition-transform duration-300" x-bind:class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" x-on:click.outside="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="display: none;" class="absolute right-0 z-50 mt-2 w-60 origin-top-right overflow-hidden rounded-3xl border border-black/10 bg-white/95 p-2 shadow-2xl shadow-black/10 backdrop-blur-xl dark:border-white/10 dark:bg-gray-950/95">
                            <div class="rounded-2xl bg-[#FCF9F4] px-4 py-3 dark:bg-white/5">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Quản lý bán hàng</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Đơn hàng, listing, khiếu nại và rút tiền.</p>
                            </div>

                            <div class="mt-2 space-y-1">
                                @foreach($managementItems as $item)
                                    <a href="{{ $item['url'] }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium transition-colors {{ $activeSection === $item['section'] ? 'bg-black text-white dark:bg-white dark:text-gray-950' : 'text-black hover:bg-[#FCF9F4] hover:text-[#D32F2F] dark:text-white dark:hover:bg-white/5 dark:hover:text-[#ff8b8b]' }}">
                                        <span class="flex items-center gap-3">
                                            <i class="{{ $item['icon'] }} text-[15px]"></i>
                                            {{ $item['label'] }}
                                        </span>
                                        <i class="fa-solid fa-arrow-right text-[11px] opacity-60"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="relative shrink-0" x-data="{ open: false }">
                        <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()" class="inline-flex items-center gap-2 rounded-full px-4 py-2 transition-colors {{ in_array($activeSection, ['application', 'shop'], true) ? 'bg-black text-white dark:bg-white dark:text-gray-950' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10' }}">
                            <i class="fa-solid fa-user-gear text-[12px]"></i>
                            Tài khoản
                            <i class="fa-solid fa-chevron-down text-[10px] opacity-70 transition-transform duration-300" x-bind:class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" x-on:click.outside="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="display: none;" class="absolute right-0 z-50 mt-2 w-56 origin-top-right overflow-hidden rounded-3xl border border-black/10 bg-white/95 p-2 shadow-2xl shadow-black/10 backdrop-blur-xl dark:border-white/10 dark:bg-gray-950/95">
                            <div class="rounded-2xl bg-[#FCF9F4] px-4 py-3 dark:bg-white/5">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Tài khoản</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Thông tin seller và phiên làm việc.</p>
                            </div>

                            <div class="mt-2 space-y-1">
                                @foreach($accountItems as $item)
                                    @if($item['section'] === 'logout')
                                        <a href="{{ $item['url'] }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-500/10">
                                            <span class="flex items-center gap-3">
                                                <i class="{{ $item['icon'] }} text-[15px]"></i>
                                                {{ $item['label'] }}
                                            </span>
                                            <i class="fa-solid fa-arrow-right text-[11px] opacity-60"></i>
                                        </a>
                                    @else
                                        <a href="{{ $item['url'] }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium transition-colors {{ $activeSection === $item['section'] ? 'bg-black text-white dark:bg-white dark:text-gray-950' : 'text-black hover:bg-[#FCF9F4] hover:text-[#D32F2F] dark:text-white dark:hover:bg-white/5 dark:hover:text-[#ff8b8b]' }}">
                                            <span class="flex items-center gap-3">
                                                <i class="{{ $item['icon'] }} text-[15px]"></i>
                                                {{ $item['label'] }}
                                            </span>
                                            <i class="fa-solid fa-arrow-right text-[11px] opacity-60"></i>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </nav>

                <div class="flex items-center gap-2 lg:hidden">
                    @if($portalSeller)
                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $portalSeller->kyc_status === \App\Enums\KycStatus::Approved ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-amber-500/10 text-amber-700 dark:text-amber-300' }}">
                            {{ $portalSeller->kyc_status?->label() ?? 'Đang chờ' }}
                        </span>
                    @endif

                    <button type="button" @click="mobileMenuOpen = ! mobileMenuOpen" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-black/10 bg-white text-gray-700 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>

            <div x-cloak x-show="mobileMenuOpen" class="border-t border-black/8 bg-white/95 px-4 py-4 dark:border-white/10 dark:bg-gray-900/95 lg:hidden">
                <div class="mx-auto max-w-7xl space-y-2">
                    @foreach($navItems as $item)
                        <a href="{{ $item['url'] }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold {{ $activeSection === $item['section'] ? 'bg-black text-white dark:bg-white dark:text-gray-950' : 'bg-slate-100 text-slate-700 dark:bg-white/5 dark:text-slate-300' }}">
                            <i class="{{ $item['icon'] }} w-4 text-center"></i>
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    <a href="{{ $logoutUrl }}" class="flex items-center gap-3 rounded-2xl bg-rose-500/10 px-4 py-3 text-sm font-semibold text-rose-700 dark:text-rose-300">
                        <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                        Đăng xuất
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>
</x-layouts.app>
