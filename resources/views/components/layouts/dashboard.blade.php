<x-layouts.app :title="$title ?? __('admin.titles.dashboard')">
    @section('pre-app-name', __('admin.brand.admin'))

    <div class="fixed inset-0 flex h-dvh min-h-0 w-full overflow-hidden">

        <x-partials.dashboard.sidebar />

        <div class="flex h-dvh min-h-0 flex-1 flex-col overflow-hidden">

            <x-partials.dashboard.header />

            <div class="flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-contain bg-slate-50 dark:bg-gray-950">

                <main class="flex-1 p-2 sm:p-4 lg:p-6">
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
