<div>
    @section('pageTitle', 'Seller Verifications (KYC)')

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'Users & Vendors', 'url' => 'javascript:void(0)'],
            ['label' => 'Seller Approved (KYC)', 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow  rounded-md w-full relative" x-data="{ zoomedImage: null }">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">Manage KYC</h4>
        </div>
        <div class="flex-auto p-4">
            <livewire:admin.table.seller-kyc.seller-kyc-table />
        </div>

        <!-- Zoom Image Overlay -->
        <div x-show="zoomedImage" style="display: none;"
             class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="zoomedImage = null"
             @keydown.escape.window="zoomedImage = null">
            <img :src="zoomedImage" class="max-w-full max-h-full rounded-lg shadow-2xl" @click.stop>
            <button @click="zoomedImage = null" class="absolute top-4 right-4 text-white hover:text-gray-300 bg-black/50 rounded-full p-2">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
    </div>

    <x-reusable.modal wire:model="showViewModal" title="KYC Details" max-width="3xl">
        @if($viewData)
            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                    <dl class="divide-y divide-slate-200 dark:divide-slate-700">

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">User</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $viewData['user_name'] }} <span class="text-slate-500 font-normal">({{ $viewData['user_email'] }})</span></dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Shop Name</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['shop_name'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Identity Number</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['cccd_number'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Status</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">
                                {!! $viewData['status_label'] !!}
                            </dd>
                        </div>

                        @if($viewData['kyc_rejected_reason'])
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Rejection Reason</dt>
                            <dd class="col-span-2 text-red-600 dark:text-red-400">{{ $viewData['kyc_rejected_reason'] }}</dd>
                        </div>
                        @endif

                        <div class="py-4">
                            <dt class="font-medium text-slate-500 dark:text-slate-400 mb-3">Identity Images</dt>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-slate-500 mb-1">Front Image</p>
                                    @if($viewData['cccd_front_image'])
                                        <button type="button" @click="zoomedImage = '{{ $viewData['cccd_front_image'] }}'" class="block w-full rounded-md border border-slate-200 dark:border-slate-700 overflow-hidden hover:opacity-90 cursor-zoom-in">
                                            <img src="{{ $viewData['cccd_front_image'] }}" alt="Front ID" class="w-full h-auto object-cover max-h-48">
                                        </button>
                                    @else
                                        <div class="w-full h-32 bg-slate-100 dark:bg-slate-800 rounded-md border border-dashed border-slate-300 dark:border-slate-600 flex items-center justify-center text-slate-400 text-xs">No image provided</div>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 mb-1">Back Image</p>
                                    @if($viewData['cccd_back_image'])
                                        <button type="button" @click="zoomedImage = '{{ $viewData['cccd_back_image'] }}'" class="block w-full rounded-md border border-slate-200 dark:border-slate-700 overflow-hidden hover:opacity-90 cursor-zoom-in">
                                            <img src="{{ $viewData['cccd_back_image'] }}" alt="Back ID" class="w-full h-auto object-cover max-h-48">
                                        </button>
                                    @else
                                        <div class="w-full h-32 bg-slate-100 dark:bg-slate-800 rounded-md border border-dashed border-slate-300 dark:border-slate-600 flex items-center justify-center text-slate-400 text-xs">No image provided</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Applied At</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['created_at'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3 border-b-0">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Updated At</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['updated_at'] }}</dd>
                        </div>

                    </dl>
                </div>
            </div>
        @endif

        <x-slot:footer>
            <button wire:click="$set('showViewModal', false)" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 transition-colors inline-flex items-center">
                <i class="fa-solid fa-xmark mr-2"></i> Close
            </button>
        </x-slot:footer>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showProcessModal" title="Process KYC #{{ $processForm->seller?->id }}" max-width="xl">
        <form id="processSellerKycForm" class="space-y-4" wire:submit="processSellerKycSubmit">
            <div class="mb-4">
                <label for="process_status" class="font-medium text-sm text-slate-600 dark:text-slate-400">Decision <span class="text-red-400">*</span></label>
                <select wire:model.live="processForm.kyc_status" id="process_status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500 dark:hover:border-slate-700" required>
                    <option value="">-- Select Decision --</option>
                    @foreach(\App\Enums\KycStatus::cases() as $statusEnum)
                        @if($statusEnum->value !== \App\Enums\KycStatus::Pending->value)
                            <option value="{{ $statusEnum->value }}">{{ $statusEnum->label() }}</option>
                        @endif
                    @endforeach
                </select>
                @error('processForm.kyc_status')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            @if((int) $processForm->kyc_status === \App\Enums\KycStatus::Rejected->value)
            <div class="mb-4">
                <label for="reject_reason" class="font-medium text-sm text-slate-600 dark:text-slate-400">Rejection Reason <span class="text-red-400">*</span></label>
                <textarea wire:model="processForm.kyc_rejected_reason" id="reject_reason" rows="3" class="form-textarea w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500 dark:hover:border-slate-700" placeholder="Please provide the reason for rejection..." required></textarea>
                @error('processForm.kyc_rejected_reason')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            @endif

            <div class="flex items-center justify-end space-x-2">
                <button wire:target="processSellerKycSubmit" type="submit" class="inline-block focus:outline-none text-blue-500 hover:bg-blue-500 hover:text-white bg-transparent border border-blue-200 dark:border-blue-700 text-sm font-medium py-1 px-3 rounded mb-1">Confirm</button>
                <button wire:click="$set('showProcessModal', false)" type="button" class="inline-block focus:outline-none text-red-500 hover:bg-red-500 hover:text-white bg-transparent border border-gray-200 dark:border-gray-700 text-sm font-medium py-1 px-3 rounded mb-1">Cancel</button>
            </div>
        </form>
    </x-reusable.modal>

    @push('scripts')
        <x-admin.swal-listener table-name="sellerKycTable" />
    @endpush
</div>
