<div>
    @section('pageTitle', 'Manage Regions')

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'Management', 'url' => 'javascript:void(0)'],
            ['label' => 'Region', 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow  rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">Manage</h4>
        </div>
        <div class="flex-auto p-4">
            <livewire:admin.table.region.region-table />
        </div>
    </div>

    <x-reusable.modal wire:model="showCreateModal" title="Create new Region" max-width="2xl">
        <form id="createRegionForm" class="space-y-4" wire:submit="createRegion">
            <div class="mb-2">
                <label for="name" class="font-medium text-sm text-slate-600 dark:text-slate-400">Name<span class="text-red-400">*</span></label>
                <input wire:model="createForm.name" type="text" id="name" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 placeholder:font-normal placeholder:text-sm hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                       placeholder="Enter region name" required>
                @error('createForm.name')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-2">
                <label for="slug" class="font-medium text-sm text-slate-600 dark:text-slate-400">Slug</label>
                <input wire:model="createForm.slug" type="text" id="slug" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 placeholder:font-normal placeholder:text-sm hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                       placeholder="Enter slug or leave blank for auto-generation">
                @error('createForm.slug')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-2">
                <label for="create_flag_code" class="font-medium text-sm text-slate-600 dark:text-slate-400">Flag Code<span class="text-red-400">*</span></label>
                <input wire:model="createForm.flag_code" type="text" id="create_flag_code" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 placeholder:font-normal placeholder:text-sm hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                       placeholder="e.g. US, UK, Global" required>
                @error('createForm.flag_code')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-2">
                <label for="parent_region" class="font-medium text-sm text-slate-600 dark:text-slate-400">Parent Region</label>
                <select wire:model="createForm.parentId" id="parent_region" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 placeholder:font-normal placeholder:text-sm hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700">
                    <option value="">--None--</option>

                    @foreach($this->parentRegions as $parentRegion)
                        <option value="{{ $parentRegion->id }}">{{ $parentRegion->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center justify-end space-x-2">
                <button wire:target="createRegion" type="submit" class="inline-block focus:outline-none text-blue-500 hover:bg-blue-500 hover:text-white bg-transparent border border-blue-200 dark:bg-transparent dark:text-blue-500 dark:hover:text-white dark:border-blue-700 dark:hover:bg-blue-500  text-sm font-medium py-1 px-3 rounded mb-1">Submit</button>
                <button wire:click="$set('showCreateModal', false)" type="button" class="inline-block focus:outline-none text-red-500 hover:bg-red-500 hover:text-white bg-transparent border border-gray-200 dark:bg-transparent dark:text-red-500 dark:hover:text-white dark:border-gray-700 dark:hover:bg-red-500  text-sm font-medium py-1 px-3 rounded mb-1">Cancel</button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showViewModal" title="Region Details" max-width="2xl">
        @if($viewData)
            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                    <dl class="divide-y divide-slate-200 dark:divide-slate-700">

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">ID</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">#{{ $viewData['id'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Name</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['name'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Slug</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['slug'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Flag Code</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['flag_code'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Parent</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['parent_name'] }}</dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Status</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">
                                {!! $viewData['status_label'] !!}
                            </dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Created At</dt>
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

    <x-reusable.modal wire:model="showEditModal" title="Edit Region #{{ $editForm->region?->id }}" max-width="2xl">
        <form id="editRegionForm" class="space-y-4" wire:submit="updateRegion">
            <div class="mb-2">
                <label for="edit_name" class="font-medium text-sm text-slate-600 dark:text-slate-400">Name<span class="text-red-400">*</span></label>
                <input wire:model="editForm.name" type="text" id="edit_name" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70" required>
                @error('editForm.name')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-2">
                <label for="edit_slug" class="font-medium text-sm text-slate-600 dark:text-slate-400">Slug</label>
                <input wire:model="editForm.slug" type="text" id="edit_slug" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70">
                @error('editForm.slug')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-2">
                <label for="edit_flag_code" class="font-medium text-sm text-slate-600 dark:text-slate-400">Flag Code<span class="text-red-400">*</span></label>
                <input wire:model="editForm.flag_code" type="text" id="edit_flag_code" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70" required>
                @error('editForm.flag_code')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-2">
                <label for="edit_parent_region" class="font-medium text-sm text-slate-600 dark:text-slate-400">Parent Region</label>
                <select wire:model="editForm.parentId" id="edit_parent_region" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0">
                    <option value="">--None--</option>
                    @foreach($this->parentRegions as $parentRegion)
                        @if($parentRegion->id !== $editForm->region?->id)
                            <option value="{{ $parentRegion->id }}">{{ $parentRegion->name }}</option>
                        @endif
                    @endforeach
                </select>
                @error('editForm.parentId')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-2">
                <label for="edit_status" class="font-medium text-sm text-slate-600 dark:text-slate-400">Status <span class="text-red-400">*</span></label>
                <select wire:model="editForm.status" id="edit_status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500 dark:hover:border-slate-700" required>
                    @foreach(\App\Enums\GeneralStatus::cases() as $statusEnum)
                        @if($statusEnum !== \App\Enums\GeneralStatus::Deleted)
                            <option value="{{ $statusEnum->value }}">{{ $statusEnum->label() }}</option>
                        @endif
                    @endforeach
                </select>
                @error('editForm.status')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-2">
                <button wire:target="updateRegion" type="submit" class="inline-block focus:outline-none text-blue-500 hover:bg-blue-500 hover:text-white bg-transparent border border-blue-200 dark:border-blue-700 text-sm font-medium py-1 px-3 rounded mb-1">Update</button>
                <button wire:click="$set('showEditModal', false)" type="button" class="inline-block focus:outline-none text-red-500 hover:bg-red-500 hover:text-white bg-transparent border border-gray-200 dark:border-gray-700 text-sm font-medium py-1 px-3 rounded mb-1">Cancel</button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showBulkStatusModal" title="Change Status for Selected Regions" max-width="md">
        <form id="bulkStatusForm" class="space-y-4" wire:submit="bulkChangeStatusRegion">

            <div class="mb-2">
                <label for="bulk_status" class="font-medium text-sm text-slate-600 dark:text-slate-400">
                    Select New Status <span class="text-red-400">*</span>
                </label>

                <select wire:model="bulkChangeStatusForm.status" id="bulk_status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500 dark:hover:border-slate-700" required>
                    <option value="">-- Select Status --</option>

                    @foreach(\App\Enums\GeneralStatus::cases() as $statusEnum)
                        @if($statusEnum !== \App\Enums\GeneralStatus::Deleted)
                            <option value="{{ $statusEnum->value }}">{{ $statusEnum->label() }}</option>
                        @endif
                    @endforeach
                </select>

                @error('bulkChangeStatusForm.status')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-2 mt-6">
                <button wire:target="bulkChangeStatusRegion" type="submit" class="inline-block focus:outline-none text-yellow-600 hover:bg-yellow-500 hover:text-white bg-transparent border border-yellow-400 dark:border-yellow-600 text-sm font-medium py-1 px-3 rounded mb-1 transition-colors">
                    Apply Status
                </button>
                <button wire:click="$set('showBulkStatusModal', false)" type="button" class="inline-block focus:outline-none text-slate-500 hover:bg-slate-500 hover:text-white bg-transparent border border-slate-300 dark:border-slate-600 text-sm font-medium py-1 px-3 rounded mb-1 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </x-reusable.modal>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('swal:confirm', (event) => {
                    const data = event[0];
                    Swal.fire({
                        title: data.title,
                        text: data.text ?? "You can not revert this action!",
                        icon: data.type ?? 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.dispatch(data.method, [data.id]);
                        }
                    });
                });

                Livewire.on('swal:success', (event) => {
                    Swal.fire({
                        title: 'Success!',
                        text: event[0].message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    Livewire.dispatch('pg:eventRefresh-regionTable');
                });

                Livewire.on('swal:error', (event) => {
                    Swal.fire({
                        title: 'Error!',
                        text: event[0].message,
                        icon: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            });
        </script>
    @endpush
</div>
