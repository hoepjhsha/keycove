<div>
    @section('pageTitle', __('admin.nav.platform_payouts'))

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'Finance & Wallet', 'url' => 'javascript:void(0)'],
            ['label' => __('admin.nav.platform_payouts'), 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-md border border-emerald-200/70 bg-white p-5 shadow dark:border-emerald-900/40 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('admin.platform_payouts.completed_amount') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['completedAmount'], 2) }} VND</p>
        </div>
        <div class="rounded-md border border-amber-200/70 bg-white p-5 shadow dark:border-amber-900/40 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('admin.platform_payouts.pending_amount') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['pendingAmount'], 2) }} VND</p>
        </div>
        <div class="rounded-md border border-rose-200/70 bg-white p-5 shadow dark:border-rose-900/40 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('admin.platform_payouts.failed_amount') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['failedAmount'], 2) }} VND</p>
        </div>
        <div class="rounded-md border border-sky-200/70 bg-white p-5 shadow dark:border-sky-900/40 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('admin.platform_payouts.preview_amount') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($preview['eligible_amount'], 2) }} VND</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $preview['eligible_count'] }} {{ __('admin.platform_payouts.eligible_items') }}</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-5 shadow dark:border-slate-700 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('admin.platform_payouts.latest_completed') }}</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ $metrics['lastCompletedAt'] ? \Carbon\Carbon::parse($metrics['lastCompletedAt'])->format('d/m/Y H:i') : '--' }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('admin.platform_payouts.total_batches') }}: {{ $metrics['batchCount'] }}</p>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-slate-800 shadow rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70 flex items-center justify-between gap-4">
            <div>
                <h4 class="font-medium">{{ __('admin.nav.platform_payouts') }}</h4>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    {{ __('admin.platform_payouts.preview_period') }}:
                    {{ $preview['period_start']->format('d/m/Y') }} - {{ $preview['period_end']->format('d/m/Y') }}
                </p>
            </div>
            <button wire:click="$refresh" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <i class="fa-solid fa-rotate mr-2"></i>{{ __('admin.common.refresh') }}
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-900/40">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">{{ __('admin.platform_payouts.payout_code') }}</th>
                        <th class="px-4 py-3">{{ __('admin.platform_payouts.period') }}</th>
                        <th class="px-4 py-3">{{ __('admin.platform_payouts.items_count') }}</th>
                        <th class="px-4 py-3">{{ __('admin.common.amount') }}</th>
                        <th class="px-4 py-3">{{ __('admin.common.status') }}</th>
                        <th class="px-4 py-3">{{ __('admin.platform_payouts.processed_at') }}</th>
                        <th class="px-4 py-3">{{ __('admin.common.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($payouts as $payout)
                        @php
                            $statusClass = match ($payout->status) {
                                \App\Enums\PlatformPayoutStatus::Completed => 'bg-emerald-500/10 text-emerald-600',
                                \App\Enums\PlatformPayoutStatus::Pending => 'bg-amber-500/10 text-amber-600',
                                \App\Enums\PlatformPayoutStatus::Processing => 'bg-sky-500/10 text-sky-600',
                                \App\Enums\PlatformPayoutStatus::Failed => 'bg-rose-500/10 text-rose-600',
                                default => 'bg-slate-500/10 text-slate-600',
                            };
                        @endphp
                        <tr class="text-sm text-slate-700 dark:text-slate-200">
                            <td class="px-4 py-3 font-medium">#{{ $payout->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900 dark:text-white">{{ $payout->payout_code }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $payout->settlement_cutoff_at?->format('d/m/Y H:i') ?? '--' }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $payout->period_start?->format('d/m/Y') }} - {{ $payout->period_end?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $payout->items_count }}</td>
                            <td class="px-4 py-3 font-semibold">{{ number_format((float) $payout->amount, 2) }} VND</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $statusClass }}">{{ $payout->status->label() }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $payout->processed_at?->format('d/m/Y H:i') ?? '--' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <button wire:click="viewPayout({{ $payout->id }})" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                        {{ __('admin.common.view_details') }}
                                    </button>

                                    @if($payout->status === \App\Enums\PlatformPayoutStatus::Pending)
                                        <button wire:click="process({{ $payout->id }})" class="rounded-md bg-emerald-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-600">
                                            {{ __('admin.platform_payouts.process') }}
                                        </button>
                                        <button wire:click="cancel({{ $payout->id }})" class="rounded-md bg-slate-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-700">
                                            {{ __('admin.platform_payouts.cancel') }}
                                        </button>
                                    @elseif($payout->status === \App\Enums\PlatformPayoutStatus::Failed)
                                        <button wire:click="retry({{ $payout->id }})" class="rounded-md bg-amber-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-amber-600">
                                            {{ __('admin.platform_payouts.retry') }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                {{ __('admin.platform_payouts.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-reusable.modal wire:model="showViewModal" :title="__('admin.modal.platform_payout_details')" max-width="4xl">
        @if($viewData)
            <div class="space-y-5">
                <div class="grid gap-5 xl:grid-cols-[1.2fr_0.8fr]">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <dl class="divide-y divide-slate-200 dark:divide-slate-700">
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">ID</dt>
                                <dd class="col-span-2 font-semibold text-slate-900 dark:text-white">#{{ $viewData['id'] }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.platform_payouts.payout_code') }}</dt>
                                <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['payout_code'] }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.platform_payouts.period') }}</dt>
                                <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['period'] }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.platform_payouts.settlement_cutoff') }}</dt>
                                <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['settlement_cutoff_at'] }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.amount') }}</dt>
                                <dd class="col-span-2 font-semibold text-slate-900 dark:text-white">{{ $viewData['amount'] }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.status') }}</dt>
                                <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['status_label'] !!}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.created_at') }}</dt>
                                <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['created_at'] }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3 border-b-0">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.platform_payouts.processed_at') }}</dt>
                                <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['processed_at'] }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-900">
                            <h4 class="font-semibold text-slate-700 dark:text-slate-300">{{ __('admin.platform_payouts.bank_information') }}</h4>
                            <dl class="mt-3 space-y-2 text-slate-700 dark:text-slate-200">
                                <div class="flex items-center justify-between gap-4"><dt>{{ __('admin.platform_payouts.bank_name') }}</dt><dd class="font-medium">{{ $viewData['bank_name'] }}</dd></div>
                                <div class="flex items-center justify-between gap-4"><dt>{{ __('admin.platform_payouts.bank_code') }}</dt><dd class="font-medium">{{ $viewData['bank_code'] }}</dd></div>
                                <div class="flex items-center justify-between gap-4"><dt>{{ __('admin.platform_payouts.account_name') }}</dt><dd class="font-medium">{{ $viewData['account_name'] }}</dd></div>
                                <div class="flex items-center justify-between gap-4"><dt>{{ __('admin.platform_payouts.account_number') }}</dt><dd class="font-medium">{{ $viewData['account_number'] }}</dd></div>
                            </dl>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-900">
                            <h4 class="font-semibold text-slate-700 dark:text-slate-300">{{ __('admin.platform_payouts.reconciliation') }}</h4>
                            <dl class="mt-3 space-y-2 text-slate-700 dark:text-slate-200">
                                <div class="flex items-center justify-between gap-4"><dt>{{ __('admin.platform_payouts.items_count') }}</dt><dd class="font-medium">{{ $viewData['items_count'] }}</dd></div>
                                <div class="flex items-center justify-between gap-4"><dt>{{ __('admin.platform_payouts.profit_type_platform_fee') }}</dt><dd class="font-medium">{{ $viewData['platform_fee_amount'] }}</dd></div>
                                <div class="flex items-center justify-between gap-4"><dt>{{ __('admin.platform_payouts.profit_type_platform_owned_sale') }}</dt><dd class="font-medium">{{ $viewData['platform_owned_amount'] }}</dd></div>
                                <div class="flex items-center justify-between gap-4"><dt>{{ __('admin.platform_payouts.requested_entry') }}</dt><dd class="font-medium">{{ $viewData['requested_entry'] ? '#'.$viewData['requested_entry'] : '--' }}</dd></div>
                                <div class="flex items-center justify-between gap-4"><dt>{{ __('admin.platform_payouts.completed_entry') }}</dt><dd class="font-medium">{{ $viewData['completed_entry'] ? '#'.$viewData['completed_entry'] : '--' }}</dd></div>
                                <div class="flex items-center justify-between gap-4"><dt>{{ __('admin.platform_payouts.failed_entry') }}</dt><dd class="font-medium">{{ $viewData['failed_entry'] ? '#'.$viewData['failed_entry'] : '--' }}</dd></div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900/60">
                    <div class="border-b border-dashed border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-300">
                        {{ __('admin.platform_payouts.items') }}
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900/40">
                                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    <th class="px-4 py-3">{{ __('admin.common.order_code') }}</th>
                                    <th class="px-4 py-3">{{ __('admin.platform_payouts.order_item_code') }}</th>
                                    <th class="px-4 py-3">{{ __('admin.platform_payouts.product') }}</th>
                                    <th class="px-4 py-3">{{ __('admin.platform_payouts.profit_type') }}</th>
                                    <th class="px-4 py-3">{{ __('admin.common.amount') }}</th>
                                    <th class="px-4 py-3">{{ __('admin.platform_payouts.completed_at') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($viewData['items'] as $item)
                                    <tr class="text-sm text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3">{{ $item['order_code'] }}</td>
                                        <td class="px-4 py-3">{{ $item['order_item_code'] }}</td>
                                        <td class="px-4 py-3">{{ $item['product_name'] }}</td>
                                        <td class="px-4 py-3">{{ $item['profit_type'] }}</td>
                                        <td class="px-4 py-3 font-medium">{{ $item['amount'] }}</td>
                                        <td class="px-4 py-3">{{ $item['completed_at'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('admin.platform_payouts.empty_items') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(!empty($viewData['metadata']))
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <h4 class="font-semibold text-slate-700 dark:text-slate-300">{{ __('admin.common.metadata') }}</h4>
                        <pre class="mt-3 overflow-x-auto whitespace-pre-wrap text-xs text-slate-700 dark:text-slate-200">{{ json_encode($viewData['metadata'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif
            </div>
        @endif

        <x-slot:footer>
            <button wire:click="$set('showViewModal', false)" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <i class="fa-solid fa-xmark mr-2"></i> Close
            </button>
        </x-slot:footer>
    </x-reusable.modal>

    @push('scripts')
        <x-admin.swal-listener table-name="platformPayouts" />
    @endpush
</div>
