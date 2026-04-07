<x-layouts.app :title="$title ?? 'Dashboard'">
    <div class="flex h-screen overflow-hidden">

        <x-partials.dashboard.sidebar />

        <div class="flex-1 flex flex-col h-screen overflow-hidden">

            <x-partials.dashboard.header />

            <div class="flex-1 overflow-y-auto flex flex-col bg-slate-50 dark:bg-gray-950">

                <main class="flex-1 p-2 sm:p-4 lg:p-6">
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $pageTitle ?? 'Dashboard' }}
                        </h1>

                        @stack('breadcrumbs')
                    </div>

                    {{ $slot }}
                </main>

                <x-partials.dashboard.footer />

            </div>

        </div>
    </div>
</x-layouts.app>
