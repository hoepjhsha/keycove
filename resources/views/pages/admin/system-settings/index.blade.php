<div>
    @section('pageTitle', 'Manage System Settings')

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'System Settings', 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow rounded-2xl border border-slate-200 dark:border-slate-700 overflow-visible">
        <div class="flex flex-col gap-4 border-b border-slate-200 dark:border-slate-700 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h4 class="text-base font-semibold text-slate-900 dark:text-slate-100">System Settings</h4>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Edit platform configuration in a compact settings layout.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <label class="relative block">
                    <span class="sr-only">Search settings</span>
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input wire:model.live.debounce.300ms="search" type="text" class="w-full sm:w-72 rounded-xl border border-slate-300 bg-white pl-9 pr-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" placeholder="Search settings">
                </label>

                <button wire:click="$set('showCreateModal', true)" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-primary-700">
                    <i class="fa-solid fa-plus"></i>
                    Add Setting
                </button>
            </div>
        </div>

        <div class="divide-y divide-slate-200 dark:divide-slate-700">
            @forelse($this->configs as $config)
                <div wire:key="system-config-{{ $config->id }}" class="flex flex-col gap-4 p-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="group relative inline-flex max-w-full">
                            <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                <i class="fa-solid fa-gear text-[11px] text-slate-400"></i>
                                <span class="font-mono truncate">{{ $config->key }}</span>
                                <i class="fa-regular fa-circle-question text-slate-400"></i>
                            </div>

                            <div class="pointer-events-none absolute left-0 top-full z-20 mt-2 hidden w-80 rounded-2xl border border-slate-200 bg-slate-950 px-3 py-2 text-xs text-slate-100 shadow-xl group-hover:block dark:border-slate-700">
                                <div class="font-semibold text-white">Description</div>
                                <div class="mt-1 leading-5 text-slate-300">{{ $config->description ?: 'No description provided for this setting.' }}</div>
                            </div>
                        </div>

                        <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">Hover the key to see what this setting controls.</div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center lg:w-[52%]">
                        <div class="flex-1">
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Value</label>
                            <input wire:model.live.debounce.500ms="inlineValues.{{ $config->id }}" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition-colors focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" />

                            @if($this->isJsonValue($config->value))
                                <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/70">
                                    <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] text-slate-700 dark:bg-slate-700 dark:text-slate-200">JSON</span>
                                        Pretty preview
                                    </div>
                                    <pre class="overflow-x-auto font-mono text-xs leading-5 text-slate-700 whitespace-pre dark:text-slate-200">{{ $this->prettyJson($config->value) }}</pre>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <button wire:click="saveInlineValue({{ $config->id }})" type="button" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">
                                Save
                            </button>
                            <button wire:click="editSystemConfig({{ $config->id }})" type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                                Edit
                            </button>
                            <button wire:click="deleteSystemConfig({{ $config->id }})" type="button" class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/10">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-700">
                        <i class="fa-solid fa-sliders text-xl"></i>
                    </div>
                    <h3 class="mt-4 text-sm font-semibold text-slate-900 dark:text-slate-100">No settings found</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Try a different search or add the first system setting.</p>
                </div>
            @endforelse
        </div>
    </div>

    <x-reusable.modal wire:model="showCreateModal" title="Create new System Setting" max-width="2xl">
        <form id="createSystemConfigForm" class="space-y-4" wire:submit="createSystemConfig">
            <div class="mb-2">
                <label for="key" class="font-medium text-sm text-slate-600 dark:text-slate-400">Key<span class="text-red-400">*</span></label>
                <input wire:model="createForm.key" type="text" id="key" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70" placeholder="site_name" required>
                @error('createForm.key')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-2">
                <label for="value" class="font-medium text-sm text-slate-600 dark:text-slate-400">Value<span class="text-red-400">*</span></label>
                <input wire:model="createForm.value" type="text" id="value" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70" required>
                @error('createForm.value')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-2">
                <label for="description" class="font-medium text-sm text-slate-600 dark:text-slate-400">Description</label>
                <textarea wire:model="createForm.description" id="description" rows="3" class="form-textarea w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 placeholder:text-slate-400/70" placeholder="Short description of this setting"></textarea>
                @error('createForm.description')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-2">
                <button wire:target="createSystemConfig" type="submit" class="inline-block focus:outline-none text-blue-500 hover:bg-blue-500 hover:text-white bg-transparent border border-blue-200 dark:bg-transparent dark:text-blue-500 dark:hover:text-white dark:border-blue-700 dark:hover:bg-blue-500 text-sm font-medium py-1 px-3 rounded mb-1">Submit</button>
                <button wire:click="$set('showCreateModal', false)" type="button" class="inline-block focus:outline-none text-red-500 hover:bg-red-500 hover:text-white bg-transparent border border-gray-200 dark:bg-transparent dark:text-red-500 dark:hover:text-white dark:border-gray-700 dark:hover:bg-red-500 text-sm font-medium py-1 px-3 rounded mb-1">Cancel</button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showViewModal" title="System Setting Details" max-width="2xl">
        @if($viewData)
            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                    <dl class="divide-y divide-slate-200 dark:divide-slate-700">
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">ID</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">#{{ $viewData['id'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Key</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['key'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Value</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white break-words">
                                @if($this->isJsonValue($viewData['value']))
                                    <pre class="overflow-x-auto rounded-xl border border-slate-200 bg-slate-950 p-3 font-mono text-xs leading-5 text-slate-100 dark:border-slate-700">{{ $this->prettyJson($viewData['value']) }}</pre>
                                @else
                                    {{ $viewData['value'] }}
                                @endif
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3 border-b-0">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Description</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['description'] ?? '--None--' }}</dd>
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

    <x-reusable.modal wire:model="showEditModal" title="Edit System Setting #{{ $editForm->systemConfig?->id }}" max-width="2xl">
        <form id="editSystemConfigForm" class="space-y-4" wire:submit="updateSystemConfig">
            <div class="mb-2">
                <label for="edit_key" class="font-medium text-sm text-slate-600 dark:text-slate-400">Key<span class="text-red-400">*</span></label>
                <input wire:model="editForm.key" type="text" id="edit_key" class="form-input w-full rounded-md mt-1 border border-slate-300/60 bg-slate-100 px-3 py-1 text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400" disabled>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Key cannot be edited.</p>
            </div>

            <div class="mb-2">
                <label for="edit_value" class="font-medium text-sm text-slate-600 dark:text-slate-400">Value<span class="text-red-400">*</span></label>
                <input wire:model="editForm.value" type="text" id="edit_value" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70" required>
                @error('editForm.value')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-2">
                <label for="edit_description" class="font-medium text-sm text-slate-600 dark:text-slate-400">Description</label>
                <textarea wire:model="editForm.description" id="edit_description" rows="3" class="form-textarea w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 placeholder:text-slate-400/70"></textarea>
                @error('editForm.description')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-2">
                <button wire:target="updateSystemConfig" type="submit" class="inline-block focus:outline-none text-blue-500 hover:bg-blue-500 hover:text-white bg-transparent border border-blue-200 dark:border-blue-700 text-sm font-medium py-1 px-3 rounded mb-1">Update</button>
                <button wire:click="$set('showEditModal', false)" type="button" class="inline-block focus:outline-none text-red-500 hover:bg-red-500 hover:text-white bg-transparent border border-gray-200 dark:border-gray-700 text-sm font-medium py-1 px-3 rounded mb-1">Cancel</button>
            </div>
        </form>
    </x-reusable.modal>

    @push('scripts')
        <x-admin.swal-listener />
    @endpush
</div>
