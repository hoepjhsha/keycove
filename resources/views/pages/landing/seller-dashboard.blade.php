<x-layouts.dashboard :title="$title ?? 'Seller Dashboard'">
    @section('pageTitle', 'Dashboard')

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow  rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">Dashboard</h4>
        </div>
        <div class="flex-auto p-4">

        </div>
    </div>
</x-layouts.dashboard>
