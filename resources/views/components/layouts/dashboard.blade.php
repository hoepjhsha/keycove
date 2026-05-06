<x-layouts.app :title="$title ?? __('admin.titles.dashboard')">
    @section('pre-app-name', __('admin.brand.admin'))

    <div class="flex h-screen min-h-0 overflow-hidden">

        <x-partials.dashboard.sidebar />

        <div class="flex flex-1 h-screen min-h-0 flex-col overflow-hidden">

            <x-partials.dashboard.header />

            <div class="flex flex-1 min-h-0 flex-col overflow-y-auto overscroll-contain bg-slate-50 dark:bg-gray-950">

                <main class="flex-1 min-h-0 p-2 sm:p-4 lg:p-6">
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $pageTitle ?? __('admin.titles.dashboard') }}
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
