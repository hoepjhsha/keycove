<div>
    @section('pageTitle', 'Escrows')

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'Orders', 'url' => 'javascript:void(0)'],
            ['label' => 'Escrow Management', 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">Manage Escrows</h4>
        </div>
        <div class="flex-auto p-4">
            <livewire:admin.table.escrow.escrow-table />
        </div>
    </div>

    <!-- View Modal -->
    <x-reusable.modal wire:model="showViewModal" title="Escrow Details" max-width="3xl">
        @if($viewData)
            <div class="space-y-4">
                <!-- Escrow Header Info -->
                <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                    <dl class="divide-y divide-slate-200 dark:divide-slate-700">
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Order Code</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">#{{ $viewData['order_code'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Amount</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $viewData['amount'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Status</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['status_badge'] !!}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3 border-b-0">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Release Date</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['release_date'] }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Buyer Info -->
                <div class="bg-blue-50 dark:bg-slate-900/50 rounded-lg p-4 text-sm border border-blue-200 dark:border-blue-900">
                    <h5 class="font-semibold text-slate-900 dark:text-white mb-3">Buyer Information</h5>
                    <dl class="divide-y divide-blue-200 dark:divide-blue-900">
                        <div class="grid grid-cols-3 gap-4 py-2">
                            <dt class="font-medium text-slate-600 dark:text-slate-400">Username</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['buyer_username'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-2 border-b-0">
                            <dt class="font-medium text-slate-600 dark:text-slate-400">Email</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['buyer_email'] }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Seller Info -->
                <div class="bg-green-50 dark:bg-slate-900/50 rounded-lg p-4 text-sm border border-green-200 dark:border-green-900">
                    <h5 class="font-semibold text-slate-900 dark:text-white mb-3">Seller Information</h5>
                    <dl class="divide-y divide-green-200 dark:divide-green-900">
                        <div class="grid grid-cols-3 gap-4 py-2 border-b-0">
                            <dt class="font-medium text-slate-600 dark:text-slate-400">Shop Name</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['seller_shop_name'] }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Timestamps -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                        <dt class="font-medium text-slate-500 dark:text-slate-400 mb-1">Created At</dt>
                        <dd class="text-slate-900 dark:text-white">{{ $viewData['created_at'] }}</dd>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                        <dt class="font-medium text-slate-500 dark:text-slate-400 mb-1">Updated At</dt>
                        <dd class="text-slate-900 dark:text-white">{{ $viewData['updated_at'] }}</dd>
                    </div>
                </div>
            </div>
        @endif
        <x-slot:footer>
            <button wire:click="$set('showViewModal', false)" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 transition-colors inline-flex items-center">
                <i class="fa-solid fa-xmark mr-2"></i> Close
            </button>
        </x-slot:footer>
    </x-reusable.modal>

    <!-- Extend Holding Modal -->
    <x-reusable.modal wire:model="showExtendModal" title="Extend Holding Time" max-width="md">
        <div class="space-y-4">
            <div class="bg-yellow-50 dark:bg-slate-900/50 rounded-lg p-4 border border-yellow-200 dark:border-yellow-900">
                <p class="text-sm text-yellow-800 dark:text-yellow-300">
                    <i class="fa-solid fa-info-circle mr-2"></i>
                    Use format: <strong>number + unit</strong> (e.g., 30s, 10m, 5h, 2d).
                </p>
                <ul class="mt-2 text-xs text-yellow-700 dark:text-yellow-400 list-disc list-inside">
                    <li><strong>s</strong>: seconds</li>
                    <li><strong>m</strong>: minutes</li>
                    <li><strong>h</strong>: hours</li>
                    <li><strong>d</strong>: days</li>
                </ul>
            </div>

            <div>
                <label for="extend_duration" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Duration to Extend
                </label>
                <input
                    type="text"
                    id="extend_duration"
                    wire:model="extendExtendHoldingForm.duration"
                    min="1"
                    max="2"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white"
                    placeholder="e.g., 1d, 3h, 30m"
                />
                @error('extendExtendHoldingForm.duration')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-3 text-sm border border-slate-200 dark:border-slate-700">
                <p class="text-slate-700 dark:text-slate-300">
                    <strong>Note:</strong> The maximum total holding time is limited to {{ \App\Constants\Admin\EscrowConstant::MAX_EXTEND_DAYS_FROM_CREATED }} days from the creation date.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <button
                wire:click="performExtendHolding"
                class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 border border-yellow-600 rounded-lg shadow-sm hover:bg-yellow-700 transition-colors inline-flex items-center">
                <i class="fa-solid fa-hourglass-end mr-2"></i> Extend
            </button>
            <button
                wire:click="$set('showExtendModal', false)"
                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 transition-colors inline-flex items-center ml-2">
                <i class="fa-solid fa-xmark mr-2"></i> Cancel
            </button>
        </x-slot:footer>
    </x-reusable.modal>

    @push('scripts')
        <x-admin.swal-listener table-name="escrowTable" />
    @endpush
</div>
