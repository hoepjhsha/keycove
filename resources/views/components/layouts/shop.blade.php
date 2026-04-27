<x-layouts.app :title="$title ?? 'Shop'">
    <div x-data="{ showBackToTop: false }"
         x-init="window.addEventListener('scroll', () => { showBackToTop = window.scrollY > 400 })"
         class="relative isolate flex min-h-screen flex-col bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-indigo-500/10 via-transparent to-transparent dark:from-indigo-400/10"></div>
        <div class="pointer-events-none absolute -top-24 right-0 h-72 w-72 rounded-full bg-blue-500/5 blur-3xl dark:bg-blue-400/5"></div>
        <div class="pointer-events-none absolute bottom-0 left-0 h-72 w-72 rounded-full bg-violet-500/5 blur-3xl dark:bg-violet-400/5"></div>

        <x-partials.shop.header />

        <main class="relative flex-1">
            {{ $slot }}
        </main>

        <button type="button"
                x-cloak
                x-show="showBackToTop"
                x-transition.opacity.duration.200ms
                x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                aria-label="Back to top"
                class="fixed bottom-5 right-5 z-50 inline-flex h-12 w-12 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-50 dark:focus-visible:ring-offset-gray-950">
            <i class="fa-solid fa-arrow-up"></i>
        </button>

        <x-partials.shop.footer />
    </div>
</x-layouts.app>
