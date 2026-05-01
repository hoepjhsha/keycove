@props([
    'offsetHeader' => true,
])

<x-layouts.app :title="$title ?? 'Cửa hàng'">
    <div x-data="{ showBackToTop: false }"
         x-init="window.addEventListener('scroll', () => { showBackToTop = window.scrollY > 400 })"
         class="relative isolate flex min-h-screen flex-col bg-[#FCF9F4] text-black font-sans antialiased dark:bg-gray-950 dark:text-gray-100">

        <x-partials.shop.header />

        <main @class([
            'relative flex-1',
            'pt-20' => $offsetHeader,
        ])>
            {{ $slot }}
        </main>

        <button type="button"
                x-cloak
                x-show="showBackToTop"
                x-transition.opacity.duration.200ms
                x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                aria-label="Lên đầu trang"
                class="fixed bottom-6 right-6 z-50 inline-flex h-12 w-12 items-center justify-center rounded-full bg-black text-white shadow-lg transition hover:-translate-y-1 hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-900 focus-visible:ring-offset-2 focus-visible:ring-offset-[#FCF9F4] dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
            <i class="fa-solid fa-arrow-up"></i>
        </button>

        <x-partials.shop.footer />
    </div>
</x-layouts.app>
