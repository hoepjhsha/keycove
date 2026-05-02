<div>
    @section('pageTitle', __('admin.nav.transactions'))

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'Finance & Wallet', 'url' => 'javascript:void(0)'],
            ['label' => 'Transactions', 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">{{ __('admin.nav.transactions') }}</h4>
        </div>
        <div class="flex-auto p-4">
            <livewire:admin.table.transaction.transaction-table />
        </div>
    </div>

    <x-reusable.modal wire:model="showViewModal" :title="__('admin.modal.transaction_details')" max-width="2xl">
        @if($viewData)
            <div class="space-y-5">
                <!-- Main Transaction Info -->
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
                            <dt class="font-medium text-slate-500 dark:text-slate-400">User</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['username'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Type</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['type_label'] !!}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Amount</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold text-lg">{{ $viewData['amount'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.status') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['status_label'] !!}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3 border-b-0">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.created_at') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['created_at'] }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Payment Info Section -->
                @if(!empty($viewData['payment_info']))
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-credit-card text-slate-600 dark:text-slate-400"></i>
                            <h4 class="font-semibold text-slate-700 dark:text-slate-300">{{ __('admin.common.payment_information') }}</h4>
                        </div>

                        <div class="space-y-3">
                            <!-- Payment Method Badge -->
                            @php
                                $method = $viewData['payment_info']['method'] ?? 'Unknown';
                                $methodIcon = match($method) {
                                    'VNPay' => 'fa-solid fa-building-columns',
                                    'Stripe' => 'fa-brands fa-cc-stripe',
                                    default => 'fa-bank'
                                };
                                $methodBgClass = match($method) {
                                    'VNPay' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700',
                                    'Stripe' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-700',
                                    default => 'bg-slate-100 dark:bg-slate-700 border-slate-300 dark:border-slate-600'
                                };
                                $methodTextClass = match($method) {
                                    'VNPay' => 'text-blue-600 dark:text-blue-400',
                                    'Stripe' => 'text-purple-600 dark:text-purple-400',
                                    default => 'text-slate-600 dark:text-slate-400'
                                };
                            @endphp

                            <div class="flex items-center gap-2 p-3 rounded-md {{ $methodBgClass }} border">
                                <i class="{{ $methodIcon }} {{ $methodTextClass }}"></i>
                                <span class="font-medium {{ $methodTextClass }}">{{ $method }}</span>
                            </div>

                            <!-- Payment Details Grid -->
                            <div class="grid grid-cols-2 gap-3 mt-3">
                                @foreach($viewData['payment_info'] as $key => $value)
                                    @if($key !== 'method' && !empty($value))
                                        <div class="bg-white dark:bg-slate-800 rounded p-3 border border-slate-200 dark:border-slate-700">
                                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                                {{ str_replace('_', ' ', $key) }}
                                            </dt>
                                            <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white break-all">
                                                @if($key === 'last4' || $key === 'account_number')
                                                    ••{{ substr((string)$value, -4) }}
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </dd>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
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
        <x-admin.swal-listener table-name="transactionTable" />
    @endpush
</div>
