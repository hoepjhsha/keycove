<div>
    @section('pageTitle', __('admin.titles.manage_complaints'))

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'Disputes', 'url' => 'javascript:void(0)'],
            ['label' => 'Complaint Centre', 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">{{ __('admin.nav.dispute_center') }}</h4>
        </div>
        <div class="flex-auto p-4">
            <livewire:admin.table.complaint.complaint-table />
        </div>
    </div>

    <x-reusable.modal wire:model="showViewModal" :title="__('admin.modal.complaint_details')" max-width="4xl">
        @if($viewData)
            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                    <dl class="divide-y divide-slate-200 dark:divide-slate-700">
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Complaint ID</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">#{{ $viewData['complaint_id'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.order_code') }}</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $viewData['order_code'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('admin.common.status') }}</dt>
                            <dd class="col-span-2">{!! $viewData['status_badge'] !!}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Product</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['product_name'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Thread</dt>
                            <dd class="col-span-2">
                                <a href="{{ $viewData['thread_url'] }}" class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200">
                                    Open thread page
                                </a>
                            </dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Reason</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['reason'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Escrow</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['escrow_status'] }}</dd>
                        </div>

                        @if(count($viewData['evidence']) > 0)
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">Evidence</dt>
                                <dd class="col-span-2">
                                    <div class="space-y-2">
                                        @foreach($viewData['evidence'] as $evidence)
                                            @if(!empty($evidence['url']))
                                                <a href="{{ $evidence['url'] }}" target="_blank" rel="noopener noreferrer" class="block text-sm text-indigo-600 hover:text-indigo-700 hover:underline break-all">
                                                    {{ $evidence['label'] }}
                                                </a>
                                            @else
                                                <span class="block text-slate-900 dark:text-white text-sm break-all">{{ $evidence['label'] }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                        <h5 class="font-semibold text-slate-900 dark:text-white mb-3">{{ __('admin.common.buyer') }}</h5>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-slate-500 dark:text-slate-400">{{ __('admin.common.username') }}</dt>
                                <dd class="text-slate-900 dark:text-white">{{ $viewData['buyer_username'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500 dark:text-slate-400">{{ __('admin.common.email') }}</dt>
                                <dd class="text-slate-900 dark:text-white break-all">{{ $viewData['buyer_email'] }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                        <h5 class="font-semibold text-slate-900 dark:text-white mb-3">Seller Information</h5>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-slate-500 dark:text-slate-400">{{ __('admin.common.username') }}</dt>
                                <dd class="text-slate-900 dark:text-white">{{ $viewData['seller_username'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500 dark:text-slate-400">{{ __('admin.common.email') }}</dt>
                                <dd class="text-slate-900 dark:text-white break-all">{{ $viewData['seller_email'] }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                    <h5 class="font-semibold text-slate-900 dark:text-white mb-3">Messages ({{ count($viewData['messages'] ?? []) }})</h5>

                    @if(!empty($viewData['messages']))
                        <div class="space-y-3">
                            @foreach($viewData['messages'] as $message)
                                <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                        <div class="font-medium text-slate-900 dark:text-white">{{ $message['sender_name'] }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $message['created_at'] }}</div>
                                    </div>

                                    <p class="text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $message['message'] }}</p>

                                    @if(!empty($message['attachments']))
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach($message['attachments'] as $attachment)
                                                <a href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-3 py-1 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600">
                                                    <i class="fa-regular fa-paperclip mr-1"></i>
                                                    {{ $attachment['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 dark:text-slate-400">No messages found for this dispute.</p>
                    @endif
                </div>

                @php
                    $selectedStatusValue = (int) ($selectedStatus ?? $viewData['status']);
                    $showUpdateSection = ! in_array((int) ($viewData['status'] ?? 0), [\App\Enums\ComplaintStatus::ApprovedRefund->value, \App\Enums\ComplaintStatus::RejectedRelease->value], true);
                    $canUpdateStatus = in_array($selectedStatusValue, [\App\Enums\ComplaintStatus::Open->value, \App\Enums\ComplaintStatus::InProcess->value, \App\Enums\ComplaintStatus::Escalated->value], true);
                    $canRefund = $selectedStatusValue === \App\Enums\ComplaintStatus::ApprovedRefund->value;
                    $canRelease = $selectedStatusValue === \App\Enums\ComplaintStatus::RejectedRelease->value;
                @endphp

                @if($showUpdateSection)
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                        <h5 class="font-semibold text-slate-900 dark:text-white mb-3">Update Status</h5>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Select Status</label>
                                <select wire:model.live="selectedStatus" class="px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm">
                                    <option value="">{{ __('admin.common.select_status') }}</option>
                                    @foreach(\App\Enums\ComplaintStatus::cases() as $statusOption)
                                        <option value="{{ $statusOption->value }}" @selected($selectedStatusValue === $statusOption->value)>
                                            {{ $statusOption->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if($canUpdateStatus)
                                <form wire:submit="updateStatus" class="flex gap-2 flex-wrap">
                                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors">
                                        <i class="fa-solid fa-check mr-2"></i>Save Status
                                    </button>
                                </form>
                            @elseif($canRefund)
                                <form wire:submit="processRefund" class="space-y-3">
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Resolution Note</label>
                                            <textarea wire:model="resolutionNote" rows="3" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm" placeholder="Write the resolution note"></textarea>
                                            @error('resolutionNote')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Resolved At</label>
                                            <input type="datetime-local" wire:model="resolvedAt" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm" />
                                            @error('resolvedAt')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium transition-colors">
                                        <i class="fa-solid fa-rotate-left mr-2"></i>Refund Buyer
                                    </button>
                                </form>
                            @elseif($canRelease)
                                <form wire:submit="processRelease" class="space-y-3">
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Resolution Note</label>
                                            <textarea wire:model="resolutionNote" rows="3" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm" placeholder="Write the resolution note"></textarea>
                                            @error('resolutionNote')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Resolved At</label>
                                            <input type="datetime-local" wire:model="resolvedAt" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm" />
                                            @error('resolvedAt')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-sm font-medium transition-colors">
                                        <i class="fa-solid fa-hand-holding-dollar mr-2"></i>Release Seller Funds
                                    </button>
                                </form>
                            @else
                                <p class="text-xs text-red-600 dark:text-red-400">
                                    Status can only be updated when Open, In Process, or Escalated is selected.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </x-reusable.modal>
</div>
