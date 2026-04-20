<div>
    @section('pageTitle', 'Manage OperatingSystems')

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'Catalog & Keys', 'url' => 'javascript:void(0)'],
            ['label' => 'Attributes', 'url' => 'javascript:void(0)'],
            ['label' => 'OperatingSystem', 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow  rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">Manage</h4>
        </div>
        <div class="flex-auto p-4">
            <livewire:admin.table.operatingSystem.operating-system-table />
        </div>
    </div>

    <x-reusable.modal wire:model="showCreateModal" title="Create new OperatingSystem" max-width="2xl">
        <form id="createOperatingSystemForm" wire:submit="createOperatingSystem">
            <div class="space-y-6">
                <div>
                    <h3 class="text-sm font-medium text-slate-900 dark:text-slate-100 pb-2 border-b border-slate-200 dark:border-slate-700">Basic Information</h3>
                    <div class="space-y-4 pt-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Name <span class="text-red-500">*</span></label>
                            <input wire:model="createForm.name" type="text" id="name"
                                   class="w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:focus:border-primary-500 transition-colors"
                                   placeholder="Enter operating system name" required aria-required="true">
                            @error('createForm.name')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="slug" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Slug</label>
                            <input wire:model="createForm.slug" type="text" id="slug"
                                   class="w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:focus:border-primary-500 transition-colors"
                                   placeholder="Enter slug or leave blank for auto-generation">
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Leave blank to auto-generate from name.</p>
                            @error('createForm.slug')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-slate-900 dark:text-slate-100 pb-2 border-b border-slate-200 dark:border-slate-700">Visual</h3>
                    <div class="space-y-4 pt-4">
                        <div>
                            <label for="create_icon_file" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Icon</label>
                            <input wire:model="createForm.icon_file" type="file" id="create_icon_file" accept=".svg,.png,.jpg,.jpeg,.webp,.gif"
                                   class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 dark:hover:file:bg-slate-700 file:cursor-pointer file:transition-colors">
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">SVG, PNG, JPG, JPEG, WEBP, GIF. Max 512KB.</p>
                            @if($createForm->icon_file)
                                <div class="mt-2 flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <img src="{{ $createForm->icon_file->temporaryUrl() }}"
                                         alt="Icon preview"
                                         class="w-12 h-12 object-contain">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">{{ $createForm->icon_file->getClientOriginalName() }}</span>
                                </div>
                            @endif
                            @error('createForm.icon_file')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                            @enderror
                            @error('createForm.icon_path')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button wire:target="createOperatingSystem" type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors focus:ring-2 focus:ring-blue-500/50 disabled:opacity-50">
                    <span wire:loading wire:target="createOperatingSystem">Saving...</span>
                    <span wire:loading.remove wire:target="createOperatingSystem">Submit</span>
                </button>
                <button wire:click="$set('showCreateModal', false)" type="button"
                        class="inline-flex items-center px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 text-sm font-medium rounded-lg border border-red-200 dark:border-red-500/30 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showViewModal" title="OperatingSystem Details" max-width="2xl">
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
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Icon</dt>
                            <dd class="col-span-2 flex items-center gap-3">
                                @if($viewData['icon_path'])
                                    <img src="{{ \App\Utilities\StorageUtility::getUrl($viewData['icon_path']) }}"
                                         alt="{{ $viewData['name'] ?? 'OperatingSystem' }} icon"
                                         class="w-12 h-12 object-contain bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="hidden w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 items-center justify-center text-slate-400">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                    <span class="text-sm text-slate-500 dark:text-slate-400 font-mono">{{ $viewData['icon_path'] }}</span>
                                @else
                                    <span class="text-sm text-slate-400 dark:text-slate-500 italic">No icon set</span>
                                @endif
                            </dd>
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

    <x-reusable.modal wire:model="showEditModal" title="Edit OperatingSystem #{{ $editForm->operatingSystem?->id }}" max-width="2xl">
        <form id="editOperatingSystemForm" wire:submit="updateOperatingSystem">
            <div class="space-y-6">
                <div>
                    <h3 class="text-sm font-medium text-slate-900 dark:text-slate-100 pb-2 border-b border-slate-200 dark:border-slate-700">Basic Information</h3>
                    <div class="space-y-4 pt-4">
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Name <span class="text-red-500">*</span></label>
                            <input wire:model="editForm.name" type="text" id="edit_name"
                                   class="w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:focus:border-primary-500 transition-colors"
                                   required aria-required="true">
                            @error('editForm.name')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="edit_slug" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Slug</label>
                            <input wire:model="editForm.slug" type="text" id="edit_slug"
                                   class="w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:focus:border-primary-500 transition-colors">
                            @error('editForm.slug')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-slate-900 dark:text-slate-100 pb-2 border-b border-slate-200 dark:border-slate-700">Visual</h3>
                    <div class="space-y-4 pt-4">
                        <div>
                            <label for="edit_icon_file" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Icon</label>
                            <input wire:model="editForm.icon_file" type="file" id="edit_icon_file" accept=".svg,.png,.jpg,.jpeg,.webp,.gif"
                                   class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 dark:hover:file:bg-slate-700 file:cursor-pointer file:transition-colors">
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">SVG, PNG, JPG, JPEG, WEBP, GIF. Max 512KB.</p>
                            @if($editForm->icon_file)
                                <div class="mt-2 flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <img src="{{ $editForm->icon_file->temporaryUrl() }}"
                                         alt="Icon preview"
                                         class="w-12 h-12 object-contain">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">{{ $editForm->icon_file->getClientOriginalName() }}</span>
                                </div>
                            @elseif($editForm->icon_path)
                                <div class="mt-2 flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <img src="{{ \App\Utilities\StorageUtility::getUrl($editForm->icon_path) }}"
                                         alt="{{ $editForm->name ?? 'OperatingSystem' }} icon"
                                         class="w-12 h-12 object-contain"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="hidden w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded flex items-center justify-center text-slate-400">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $editForm->icon_path }}</span>
                                </div>
                            @endif
                            @error('editForm.icon_file')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-slate-900 dark:text-slate-100 pb-2 border-b border-slate-200 dark:border-slate-700">Status</h3>
                    <div class="pt-4">
                        <label for="edit_status" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select wire:model="editForm.status" id="edit_status"
                                class="w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:focus:border-primary-500 transition-colors"
                                required aria-required="true">
                            @foreach(\App\Enums\GeneralStatus::cases() as $statusEnum)
                                @if($statusEnum !== \App\Enums\GeneralStatus::Deleted)
                                    <option value="{{ $statusEnum->value }}">{{ $statusEnum->label() }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('editForm.status')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button wire:target="updateOperatingSystem" type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors focus:ring-2 focus:ring-blue-500/50 disabled:opacity-50">
                    <span wire:loading wire:target="updateOperatingSystem">Saving...</span>
                    <span wire:loading.remove wire:target="updateOperatingSystem">Update</span>
                </button>
                <button wire:click="$set('showEditModal', false)" type="button"
                        class="inline-flex items-center px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 text-sm font-medium rounded-lg border border-red-200 dark:border-red-500/30 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showBulkStatusModal" title="Change Status for Selected OperatingSystems" max-width="md">
        <form id="bulkStatusForm" class="space-y-4" wire:submit="bulkChangeStatusOperatingSystem">

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
                <button wire:target="bulkChangeStatusOperatingSystem" type="submit" class="inline-block focus:outline-none text-yellow-600 hover:bg-yellow-500 hover:text-white bg-transparent border border-yellow-400 dark:border-yellow-600 text-sm font-medium py-1 px-3 rounded mb-1 transition-colors">
                    Apply Status
                </button>
                <button wire:click="$set('showBulkStatusModal', false)" type="button" class="inline-block focus:outline-none text-slate-500 hover:bg-slate-500 hover:text-white bg-transparent border border-slate-300 dark:border-slate-600 text-sm font-medium py-1 px-3 rounded mb-1 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </x-reusable.modal>

    @push('scripts')
        <x-admin.swal-listener table-name="operatingSystemTable" />
    @endpush
</div>
