<div>
    @section('pageTitle', __('admin.nav.withdrawal_requests'))

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'Finance & Wallet', 'url' => 'javascript:void(0)'],
            ['label' => __('admin.nav.withdrawal_requests'), 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">{{ __('admin.nav.withdrawal_requests') }}</h4>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-900/40">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">{{ __('admin.common.seller') }}</th>
                        <th class="px-4 py-3">{{ __('admin.common.amount') }}</th>
                        <th class="px-4 py-3">{{ __('admin.common.status') }}</th>
                        <th class="px-4 py-3">{{ __('admin.common.created_at_short') }}</th>
                        <th class="px-4 py-3">{{ __('admin.common.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($withdrawals as $withdrawal)
                        @php
                            $statusClass = match ($withdrawal->status) {
                                \App\Enums\WithdrawStatus::Completed => 'bg-emerald-500/10 text-emerald-600',
                                \App\Enums\WithdrawStatus::Pending => 'bg-amber-500/10 text-amber-600',
                                \App\Enums\WithdrawStatus::Processing => 'bg-sky-500/10 text-sky-600',
                                \App\Enums\WithdrawStatus::Rejected, \App\Enums\WithdrawStatus::Failed => 'bg-rose-500/10 text-rose-600',
                                default => 'bg-slate-500/10 text-slate-600',
                            };

                            $sellerName = $withdrawal->wallet?->seller?->shop_name
                                ?? $withdrawal->wallet?->seller?->user?->username
                                ?? __('admin.common.unknown');
                        @endphp

                        <tr class="text-sm text-slate-700 dark:text-slate-200">
                            <td class="px-4 py-3 font-medium">#{{ $withdrawal->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900 dark:text-white">{{ $sellerName }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $withdrawal->bank_name }} · {{ $withdrawal->bank_account_name }}</div>
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ number_format((float) $withdrawal->amount, 2) }} VND</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $statusClass }}">{{ $withdrawal->status->label() }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $withdrawal->created_at?->format('d/m/Y H:i') ?? '--' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button wire:click="viewWithdrawal({{ $withdrawal->id }})" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                        {{ __('admin.common.view_details') }}
                                    </button>

                                    @if($withdrawal->status === \App\Enums\WithdrawStatus::Pending)
                                        <button wire:click="approve({{ $withdrawal->id }})" class="rounded-md bg-emerald-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-600">
                                            {{ __('admin.common.approve') }}
                                        </button>
                                        <button wire:click="openRejectModal({{ $withdrawal->id }})" class="rounded-md bg-rose-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-rose-600">
                                            {{ __('admin.common.reject') }}
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $withdrawal->processed_at?->format('d/m/Y H:i') ?? '--' }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                {{ __('admin.withdrawals.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-reusable.modal wire:model="showViewModal" :title="__('admin.modal.withdrawal_details')" max-width="2xl">
        @if($viewData)
            <div class="space-y-5">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <dl class="divide-y divide-slate-200 dark:divide-slate-700">
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">ID</dt>
                            <dd class="col-span-2 font-semibold text-slate-900 dark:text-white">#{{ $viewData['id'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Người bán</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['seller_name'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Người yêu cầu</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['requested_by'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Người xử lý</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['processed_by'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Số tiền</dt>
                            <dd class="col-span-2 font-semibold text-slate-900 dark:text-white">{{ $viewData['amount'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Trạng thái</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['status_label'] !!}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Ngân hàng</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['bank_name'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Mã ngân hàng</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['bank_code'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Tên tài khoản</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['account_name'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Số tài khoản</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['account_number'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Lý do từ chối</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['reject_reason'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Xử lý lúc</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['processed_at'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3 border-b-0">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Tạo lúc</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['created_at'] }}</dd>
                        </div>
                    </dl>
                </div>

                @if(!empty($viewData['metadata']))
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <h4 class="font-semibold text-slate-700 dark:text-slate-300">Metadata</h4>
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

    <x-reusable.modal wire:model="showRejectModal" :title="__('admin.common.reject')" max-width="lg">
        <div class="space-y-4">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="rejectReason">
                {{ __('admin.common.reason') }}
            </label>
            <textarea id="rejectReason" wire:model="rejectReason" rows="4" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"></textarea>
            @error('rejectReason')
                <p class="text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <x-slot:footer>
            <div class="flex items-center gap-3">
                <button wire:click="$set('showRejectModal', false)" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 transition-colors">
                    {{ __('admin.common.cancel') }}
                </button>
                <button wire:click="reject" class="px-4 py-2 text-sm font-medium text-white bg-rose-500 border border-rose-500 rounded-lg shadow-sm hover:bg-rose-600 transition-colors">
                    {{ __('admin.common.reject') }}
                </button>
            </div>
        </x-slot:footer>
    </x-reusable.modal>

    @push('scripts')
        <x-admin.swal-listener table-name="withdrawalRequests" />
    @endpush
</div>
