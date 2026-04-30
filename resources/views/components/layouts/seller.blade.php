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

    $shopHomeUrl = \Illuminate\Support\Facades\Route::has('app.shop.index')
        ? route('app.shop.index')
        : url('/');

    $logoutUrl = \Illuminate\Support\Facades\Route::has('app.auth.logout')
        ? route('app.auth.logout')
        : url('/auth/login');

    $navItems = [
        ['label' => 'Dashboard', 'url' => $dashboardUrl, 'section' => 'dashboard', 'icon' => 'fa-solid fa-chart-line'],
        ['label' => 'Application', 'url' => $applicationUrl, 'section' => 'application', 'icon' => 'fa-regular fa-id-card'],
        ['label' => 'Listings', 'url' => $listingsUrl, 'section' => 'listings', 'icon' => 'fa-solid fa-tags'],
        ['label' => 'Back to shop', 'url' => $shopHomeUrl, 'section' => 'shop', 'icon' => 'fa-solid fa-house'],
    ];
@endphp

<x-layouts.app :title="$title ?? 'Seller Portal'">
    @section('pre-app-name', 'Seller')

    <div x-data="{ mobileMenuOpen: false }" class="min-h-screen bg-[#FCF9F4] text-gray-950 dark:bg-gray-950 dark:text-gray-100">
        <header class="sticky top-0 z-40 border-b border-black/8 bg-white/90 backdrop-blur dark:border-white/10 dark:bg-gray-900/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-black text-white shadow-sm dark:bg-white dark:text-gray-950">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] dark:text-[#ff9c9c]">Seller Portal</p>
                        <h1 class="truncate text-lg font-semibold text-gray-950 dark:text-white">{{ $portalSeller?->shop_name ?: ($portalUser?->username ?? 'Seller Portal') }}</h1>
                    </div>
                </div>

                <nav class="hidden flex-wrap items-center gap-2 text-sm font-semibold lg:flex">
                    @foreach($navItems as $item)
                        <a href="{{ $item['url'] }}" class="inline-flex items-center gap-2 rounded-full px-4 py-2 transition-colors {{ $activeSection === $item['section'] ? 'bg-black text-white dark:bg-white dark:text-gray-950' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10' }}">
                            <i class="{{ $item['icon'] }} text-[12px]"></i>
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    <a href="{{ $logoutUrl }}" class="inline-flex items-center gap-2 rounded-full bg-rose-500/10 px-4 py-2 text-rose-700 transition-colors hover:bg-rose-500/15 dark:text-rose-300">
                        <i class="fa-solid fa-right-from-bracket text-[12px]"></i>
                        Logout
                    </a>
                </nav>

                <div class="flex items-center gap-2 lg:hidden">
                    @if($portalSeller)
                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $portalSeller->kyc_status === \App\Enums\KycStatus::Approved ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-amber-500/10 text-amber-700 dark:text-amber-300' }}">
                            {{ $portalSeller->kyc_status?->label() ?? 'Pending' }}
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
                        Logout
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>
</x-layouts.app>
