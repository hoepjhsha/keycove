<x-layouts.app :title="$title ?? 'Shop'">
    <div class="flex flex-col min-h-screen bg-white dark:bg-gray-950">
        {{-- Shop Header --}}
        <x-partials.shop.header />

        {{-- Main Content --}}
        <main class="flex-grow">
            {{ $slot }}
        </main>

        {{-- Shop Footer --}}
        <x-partials.shop.footer />

        {{-- Cart Sidebar/Drawer (Optional, can be added later) --}}
    </div>
</x-layouts.app>
