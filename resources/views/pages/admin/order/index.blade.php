<div>
    @section('pageTitle', __('admin.titles.manage_orders'))

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => __('admin.nav.orders'), 'url' => 'javascript:void(0)'],
            ['label' => 'Orders History', 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">{{ __('admin.titles.manage_orders') }}</h4>
        </div>
        <div class="flex-auto p-4">
            <livewire:admin.table.order.order-table />
        </div>
    </div>

    <x-reusable.modal wire:model="showViewModal" :title="__('admin.titles.order_details')" max-width="3xl">
        @if($viewData)
            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                    <dl class="divide-y divide-slate-200 dark:divide-slate-700">
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.order_code') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">#{{ $viewData['order_code'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.status') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['status_badge'] !!}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.payment_method') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['payment_badge'] !!}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.payment_status') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['payment_status_badge'] !!}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.total_price') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $viewData['total_price'] }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-blue-50 dark:bg-slate-900/50 rounded-lg p-4 text-sm border border-blue-200 dark:border-blue-900">
                    <h5 class="font-semibold text-slate-900 dark:text-white mb-3">{{ __('admin.common.buyer') }}</h5>
                    <dl class="divide-y divide-blue-200 dark:divide-blue-900">
                        <div class="grid grid-cols-3 gap-4 py-2">
                            <dt class="font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.username') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['buyer_username'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-2 border-b-0">
                            <dt class="font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.email') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['buyer_email'] }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                        <dt class="font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('admin.common.created_at') }}</dt>
                        <dd class="text-slate-900 dark:text-white">{{ $viewData['created_at'] }}</dd>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                        <dt class="font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('admin.common.updated_at') }}</dt>
                        <dd class="text-slate-900 dark:text-white">{{ $viewData['updated_at'] }}</dd>
                    </div>
                </div>
            </div>
        @endif
        <x-slot:footer>
            <a href="{{ $viewData['order_url'] ?? '#' }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 transition-colors inline-flex items-center">
                <i class="fa-solid fa-arrow-right mr-2"></i> View Full Details
            </a>
            <button wire:click="$set('showViewModal', false)" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 transition-colors inline-flex items-center ml-2">
                <i class="fa-solid fa-xmark mr-2"></i> Close
            </button>
        </x-slot:footer>
    </x-reusable.modal>

    @push('scripts')
        <x-admin.swal-listener table-name="orderTable" />
    @endpush
</div>
