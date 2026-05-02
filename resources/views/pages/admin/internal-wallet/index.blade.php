<div>
    @section('pageTitle', __('admin.nav.internal_wallet'))

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'Finance & Wallet', 'url' => 'javascript:void(0)'],
            ['label' => __('admin.nav.internal_wallet'), 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-md border border-emerald-200/70 bg-white p-5 shadow dark:border-emerald-900/40 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.cash_in') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['cashIn'], 2) }} VND</p>
        </div>
        <div class="rounded-md border border-rose-200/70 bg-white p-5 shadow dark:border-rose-900/40 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.cash_out') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['cashOut'], 2) }} VND</p>
        </div>
        <div class="rounded-md border border-sky-200/70 bg-white p-5 shadow dark:border-sky-900/40 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.net_balance') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['netBalance'], 2) }} VND</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-5 shadow dark:border-slate-700 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.seller_available_liability') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['sellerAvailableLiability'], 2) }} VND</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-5 shadow dark:border-slate-700 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.seller_holding_liability') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['sellerHoldingLiability'], 2) }} VND</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-5 shadow dark:border-slate-700 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.pending_withdrawal_liability') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['pendingWithdrawalLiability'], 2) }} VND</p>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-slate-800 shadow rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">{{ __('admin.nav.internal_wallet') }}</h4>
        </div>
        <div class="flex-auto p-4">
            <livewire:admin.table.internal-wallet.internal-wallet-table />
        </div>
    </div>

    <x-reusable.modal wire:model="showViewModal" :title="__('admin.modal.internal_wallet_entry_details')" max-width="2xl">
        @if($viewData)
            <div class="space-y-5">
                <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                    <dl class="divide-y divide-slate-200 dark:divide-slate-700">
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">ID</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">#{{ $viewData['id'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.order_code') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['order_code'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.user') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['buyer_username'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.wallet') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['wallet_code'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.type') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['type_label'] !!}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.direction') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['direction_label'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.amount') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold text-lg">{{ $viewData['amount'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.status') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['status_label'] !!}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.affects_balance') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['affects_balance'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.source') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['source_reference'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.occurred_at') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['occurred_at'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3 border-b-0">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.created_at') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['created_at'] }}</dd>
                        </div>
                    </dl>
                </div>

                @if(!empty($viewData['metadata']))
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                        <h4 class="font-semibold text-slate-700 dark:text-slate-300">{{ __('admin.common.metadata') }}</h4>
                        <pre class="mt-3 overflow-x-auto whitespace-pre-wrap text-xs text-slate-700 dark:text-slate-200">{{ json_encode($viewData['metadata'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif
            </div>
        @endif

        <x-slot:footer>
            <button wire:click="$set('showViewModal', false)" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 transition-colors inline-flex items-center">
                <i class="fa-solid fa-xmark mr-2"></i> Close
            </button>
        </x-slot:footer>
    </x-reusable.modal>

    @push('scripts')
        <x-admin.swal-listener table-name="internalWalletTable" />
    @endpush
</div>
