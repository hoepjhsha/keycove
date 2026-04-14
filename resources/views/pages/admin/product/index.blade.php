<div>
    @section('pageTitle', 'Manage Products')

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'Management', 'url' => 'javascript:void(0)'],
            ['label' => 'Product', 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow  rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">Manage</h4>
        </div>
        <div class="flex-auto p-4">
            <livewire:admin.table.product.product-table />
        </div>
    </div>

    <x-reusable.modal wire:model="showCreateModal" title="Create new Product" max-width="4xl">
        <form id="createProductForm" class="space-y-4" wire:submit="createProduct">
            <!-- Name & Slug Row -->
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-2">
                    <label for="name" class="font-medium text-sm text-slate-600 dark:text-slate-400">Name<span class="text-red-400">*</span></label>
                    <input wire:model="createForm.name" type="text" id="name" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 placeholder:font-normal placeholder:text-sm hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                           placeholder="Enter product name" required>
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
            </div>

            <!-- Publisher & Developer Row -->
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-2">
                    <label for="publisher" class="font-medium text-sm text-slate-600 dark:text-slate-400">Publisher</label>
                    <input wire:model="createForm.publisher" type="text" id="publisher" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                           placeholder="Enter publisher name">
                    @error('createForm.publisher')
                        <small class="error block text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
                <div class="mb-2">
                    <label for="developer" class="font-medium text-sm text-slate-600 dark:text-slate-400">Developer</label>
                    <input wire:model="createForm.developer" type="text" id="developer" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                           placeholder="Enter developer name">
                    @error('createForm.developer')
                        <small class="error block text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Release Date & Status Row -->
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-2">
                    <label for="release_date" class="font-medium text-sm text-slate-600 dark:text-slate-400">Release Date</label>
                    <input wire:model="createForm.release_date" type="date" id="release_date" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700">
                    @error('createForm.release_date')
                        <small class="error block text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
                <div class="mb-2">
                    <label for="create_status" class="font-medium text-sm text-slate-600 dark:text-slate-400">Status <span class="text-red-400">*</span></label>
                    <select wire:model="createForm.status" id="create_status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500 dark:hover:border-slate-700" required>
                        @foreach(\App\Enums\GeneralStatus::cases() as $statusEnum)
                            @if($statusEnum !== \App\Enums\GeneralStatus::Deleted)
                                <option value="{{ $statusEnum->value }}">{{ $statusEnum->label() }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('createForm.status')
                        <small class="error block text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mb-2">
                <label for="description" class="font-medium text-sm text-slate-600 dark:text-slate-400">Description</label>
                <textarea wire:model="createForm.description" id="description" rows="3" class="form-textarea w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                          placeholder="Enter product description"></textarea>
                @error('createForm.description')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <!-- Image Upload with Preview -->
            <div class="mb-2">
                <label for="image" class="font-medium text-sm text-slate-600 dark:text-slate-400">Thumbnail Image</label>
                <div class="mt-2 flex items-start gap-4">
                    <div class="flex-1">
                        <input wire:model.live="createForm.image" type="file" id="image" accept="image/*" class="form-input w-full rounded-md border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700">
                        <small class="text-slate-500 dark:text-slate-400 text-xs mt-1 block">Max size: 2MB (PNG, JPG, GIF)</small>
                    </div>
                    @if($createForm->image)
                        <div class="flex flex-col items-center gap-2">
                            <img src="{{ $createForm->image?->temporaryUrl() ?? '' }}" alt="preview" class="w-24 h-24 object-cover rounded-md border border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="$set('createForm.image', null)" class="text-xs text-red-500 hover:text-red-700 font-medium">Remove</button>
                        </div>
                    @endif
                </div>
                @error('createForm.image')
                    <small class="error block text-red-500 text-xs mt-1">{{ $message }}</small>
                @enderror
            </div>

            <!-- Categories Multi-Select -->
            <div class="mb-2">
                <label for="categories" class="font-medium text-sm text-slate-600 dark:text-slate-400">Categories</label>
                <select wire:model="createForm.categories" id="categories" multiple class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500 dark:hover:border-slate-700 min-h-[120px]">
                    <!-- Parent categories (allow selection) -->
                    @foreach($categoriesGrouped as $parentId => $parentData)
                        <option value="{{ $parentId }}" style="font-weight: bold;">{{ $parentData['name'] }} (Parent)</option>
                        @foreach($parentData['children'] as $child)
                            <option value="{{ $child->id }}" style="padding-left: 20px;">— {{ $child->name }}</option>
                        @endforeach
                    @endforeach
                </select>
                <small class="text-slate-500 dark:text-slate-400 text-xs mt-1 block">Hold Ctrl/Cmd to select multiple categories</small>
                @error('createForm.categories')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <!-- System Requirements Key-Value -->
            <div class="mb-4">
                <label class="font-medium text-sm text-slate-600 dark:text-slate-400">System Requirements</label>
                <div class="mt-2 space-y-2">
                    @foreach($createForm->systemRequirements as $index => $item)
                        <div class="flex gap-2 items-start" wire:key="sys-req-{{ $index }}">
                            <input type="text"
                                   wire:model.live="createForm.systemRequirements.{{ $index }}.key"
                                   placeholder="Key (e.g., os, ram, storage)"
                                   class="flex-1 form-input rounded-md border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                                   x-ref="key_{{ $index }}">
                            <input type="text"
                                   wire:model.live="createForm.systemRequirements.{{ $index }}.value"
                                   placeholder="Value (e.g., Windows 10, 8GB, 50GB)"
                                   class="flex-1 form-input rounded-md border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700">
                            @if(count($createForm->systemRequirements) > 1)
                                <button type="button"
                                        wire:click="removeSystemRequirement({{ $index }})"
                                        class="px-3 py-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md font-medium text-sm transition-colors">
                                    ✕
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>

                <button type="button"
                        wire:click="addSystemRequirement()"
                        class="mt-2 inline-flex items-center text-sm font-medium text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                    <i class="fas fa-plus mr-1"></i> Add Row
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button wire:target="createProduct" type="submit" class="inline-block focus:outline-none text-blue-500 hover:bg-blue-500 hover:text-white bg-transparent border border-blue-200 dark:bg-transparent dark:text-blue-500 dark:hover:text-white dark:border-blue-700 dark:hover:bg-blue-500  text-sm font-medium py-1 px-3 rounded mb-1">Submit</button>
                <button wire:click="$set('showCreateModal', false)" type="button" class="inline-block focus:outline-none text-red-500 hover:bg-red-500 hover:text-white bg-transparent border border-gray-200 dark:bg-transparent dark:text-red-500 dark:hover:text-white dark:border-gray-700 dark:hover:bg-red-500  text-sm font-medium py-1 px-3 rounded mb-1">Cancel</button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showViewModal" title="Product Details" max-width="4xl">
        @if($viewData)
            <div class="space-y-6">
                <!-- Header Section: ID & Status -->
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Product ID</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white">#{{ $viewData['id'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Status</p>
                        <div class="mt-1">{!! $viewData['status_label'] !!}</div>
                    </div>
                </div>

                <!-- Image & Basic Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Product Image -->
                     <div class="md:col-span-1">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-3">Product Image</p>
                        @if($viewData['image_url'])
                            <div class="bg-slate-100 dark:bg-slate-800 rounded-lg p-3 flex items-center justify-center min-h-[200px] overflow-auto">
                                <img src="{{ $viewData['image_url'] }}" alt="{{ $viewData['name'] }}" style="max-width: 100%; max-height: 100%; object-fit: contain;" class="rounded">
                            </div>
                        @else
                            <div class="bg-slate-100 dark:bg-slate-800 rounded-lg p-3 flex items-center justify-center min-h-[200px]">
                                <div class="text-center">
                                    <i class="fa-solid fa-image text-4xl text-slate-300 dark:text-slate-600 mb-2"></i>
                                    <p class="text-slate-500 dark:text-slate-400 text-xs">No Image</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Basic Info -->
                    <div class="md:col-span-2 space-y-4">
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Name</p>
                            <p class="text-slate-900 dark:text-white font-medium">{{ $viewData['name'] }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Slug</p>
                            <p class="text-slate-900 dark:text-white text-sm">{{ $viewData['slug'] }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Publisher</p>
                                <p class="text-slate-900 dark:text-white text-sm">{{ $viewData['publisher'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Developer</p>
                                <p class="text-slate-900 dark:text-white text-sm">{{ $viewData['developer'] }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Release Date</p>
                            <p class="text-slate-900 dark:text-white text-sm">{{ $viewData['release_date'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-slate-200 dark:border-slate-700"></div>

                <!-- Categories Section -->
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-3">Categories</p>
                    @if($viewData['categories']->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach($viewData['categories'] as $category)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 dark:text-slate-400 text-sm">No categories assigned</p>
                    @endif
                </div>

                <!-- Description Section -->
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-3">Description</p>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 text-sm text-slate-900 dark:text-slate-300 max-h-48 overflow-y-auto border border-slate-200 dark:border-slate-700">
                        @if($viewData['description'] !== '--N/A--')
                            {!! nl2br(e($viewData['description'])) !!}
                        @else
                            <span class="text-slate-500 dark:text-slate-400 italic">No description provided</span>
                        @endif
                    </div>
                </div>

                <!-- System Requirements Section -->
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-3">System Requirements</p>
                    @if(!empty($viewData['system_requirement']))
                        <div class="space-y-2">
                            @foreach($viewData['system_requirement'] as $key => $value)
                                <div class="flex items-start justify-between bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                    <span class="text-sm text-slate-900 dark:text-white font-medium">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 dark:text-slate-400 text-sm">No system requirements specified</p>
                    @endif
                </div>

                <!-- Metadata Section -->
                <div class="bg-slate-50 dark:bg-slate-800/30 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-3">Metadata</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 mb-1">Created</p>
                            <p class="text-slate-900 dark:text-white font-medium">{{ $viewData['created_at'] }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 mb-1">Updated</p>
                            <p class="text-slate-900 dark:text-white font-medium">{{ $viewData['updated_at'] }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 mb-1">Deleted</p>
                            <p class="text-slate-900 dark:text-white font-medium">
                                @if($viewData['deleted_at'])
                                    {{ $viewData['deleted_at'] }}
                                @else
                                    <span class="text-green-600 dark:text-green-400">Active</span>
                                @endif
                            </p>
                        </div>
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

    <x-reusable.modal wire:model="showEditModal" title="Edit Product #{{ $editForm->product?->id }}" max-width="4xl">
        <form id="editProductForm" class="space-y-4" wire:submit="updateProduct">
            <!-- Name & Slug Row -->
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-2">
                    <label for="edit_name" class="font-medium text-sm text-slate-600 dark:text-slate-400">Name<span class="text-red-400">*</span></label>
                    <input wire:model="editForm.name" type="text" id="edit_name" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 placeholder:font-normal placeholder:text-sm hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                           placeholder="Enter product name" required>
                    @error('editForm.name')
                        <small class="error block text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
                <div class="mb-2">
                    <label for="edit_slug" class="font-medium text-sm text-slate-600 dark:text-slate-400">Slug</label>
                    <input wire:model="editForm.slug" type="text" id="edit_slug" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 placeholder:font-normal placeholder:text-sm hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                           placeholder="Enter slug or leave blank for auto-generation">
                    @error('editForm.slug')
                        <small class="error block text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Publisher & Developer Row -->
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-2">
                    <label for="edit_publisher" class="font-medium text-sm text-slate-600 dark:text-slate-400">Publisher</label>
                    <input wire:model="editForm.publisher" type="text" id="edit_publisher" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                           placeholder="Enter publisher name">
                    @error('editForm.publisher')
                        <small class="error block text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
                <div class="mb-2">
                    <label for="edit_developer" class="font-medium text-sm text-slate-600 dark:text-slate-400">Developer</label>
                    <input wire:model="editForm.developer" type="text" id="edit_developer" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                           placeholder="Enter developer name">
                    @error('editForm.developer')
                        <small class="error block text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Release Date & Status Row -->
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-2">
                    <label for="edit_release_date" class="font-medium text-sm text-slate-600 dark:text-slate-400">Release Date</label>
                    <input wire:model="editForm.release_date" type="date" id="edit_release_date" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700">
                    @error('editForm.release_date')
                        <small class="error block text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
                <div class="mb-2">
                    <label for="edit_status" class="font-medium text-sm text-slate-600 dark:text-slate-400">Status <span class="text-red-400">*</span></label>
                    <select wire:model="editForm.status" id="edit_status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500 dark:hover:border-slate-700" required>
                        @foreach(\App\Enums\GeneralStatus::cases() as $statusEnum)
                            @if($statusEnum !== \App\Enums\GeneralStatus::Deleted)
                                <option value="{{ $statusEnum->value }}">{{ $statusEnum->label() }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('editForm.status')
                        <small class="error block text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mb-2">
                <label for="edit_description" class="font-medium text-sm text-slate-600 dark:text-slate-400">Description</label>
                <textarea wire:model="editForm.description" id="edit_description" rows="3" class="form-textarea w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                          placeholder="Enter product description"></textarea>
                @error('editForm.description')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <!-- Image Upload with Preview -->
            <div class="mb-2">
                <label for="edit_image" class="font-medium text-sm text-slate-600 dark:text-slate-400">Thumbnail Image</label>
                <div class="mt-2 flex items-start gap-4">
                    <div class="flex-1">
                        <input wire:model.live="editForm.image" type="file" id="edit_image" accept="image/*" class="form-input w-full rounded-md border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700">
                        <small class="text-slate-500 dark:text-slate-400 text-xs mt-1 block">Max size: 2MB (PNG, JPG, GIF). Leave empty to keep current image.</small>
                    </div>
                    @if($editForm->image)
                        <div class="flex flex-col items-center gap-2">
                            <img src="{{ $editForm->image?->temporaryUrl() ?? '' }}" alt="preview" class="w-24 h-24 object-cover rounded-md border border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="$set('editForm.image', null)" class="text-xs text-red-500 hover:text-red-700 font-medium">Remove</button>
                        </div>
                    @elseif($editForm->product?->image_thumbnail_path)
                        <div class="flex flex-col items-center gap-2">
                            <img src="{{ \App\Utilities\StorageUtility::getUrl($editForm->product->image_thumbnail_path) }}" alt="current" class="w-24 h-24 object-cover rounded-md border border-slate-200 dark:border-slate-700">
                            <small class="text-slate-500 dark:text-slate-400 text-xs">Current Image</small>
                        </div>
                    @endif
                </div>
                @error('editForm.image')
                    <small class="error block text-red-500 text-xs mt-1">{{ $message }}</small>
                @enderror
            </div>

            <!-- Categories Multi-Select -->
            <div class="mb-2">
                <label for="edit_categories" class="font-medium text-sm text-slate-600 dark:text-slate-400">Categories</label>
                <select wire:model="editForm.categories" id="edit_categories" multiple class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500 dark:hover:border-slate-700 min-h-[120px]">
                    <!-- Parent categories (allow selection) -->
                    @foreach($categoriesGrouped as $parentId => $parentData)
                        <option value="{{ $parentId }}" style="font-weight: bold;">{{ $parentData['name'] }} (Parent)</option>
                        @foreach($parentData['children'] as $child)
                            <option value="{{ $child->id }}" style="padding-left: 20px;">— {{ $child->name }}</option>
                        @endforeach
                    @endforeach
                </select>
                <small class="text-slate-500 dark:text-slate-400 text-xs mt-1 block">Hold Ctrl/Cmd to select multiple categories</small>
                @error('editForm.categories')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <!-- System Requirements Key-Value -->
            <div class="mb-4">
                <label class="font-medium text-sm text-slate-600 dark:text-slate-400">System Requirements</label>
                <div class="mt-2 space-y-2">
                    @foreach($editForm->systemRequirements as $index => $item)
                        <div class="flex gap-2 items-start" wire:key="edit-sys-req-{{ $index }}">
                            <input type="text"
                                   wire:model.live="editForm.systemRequirements.{{ $index }}.key"
                                   placeholder="Key (e.g., os, ram, storage)"
                                   class="flex-1 form-input rounded-md border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700"
                                   x-ref="key_{{ $index }}">
                            <input type="text"
                                   wire:model.live="editForm.systemRequirements.{{ $index }}.value"
                                   placeholder="Value (e.g., Windows 10, 8GB, 50GB)"
                                   class="flex-1 form-input rounded-md border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500  dark:hover:border-slate-700">
                            @if(count($editForm->systemRequirements) > 1)
                                <button type="button"
                                        wire:click="removeSystemRequirement({{ $index }})"
                                        class="px-3 py-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md font-medium text-sm transition-colors">
                                    ✕
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>

                <button type="button"
                        wire:click="addSystemRequirement()"
                        class="mt-2 inline-flex items-center text-sm font-medium text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                    <i class="fas fa-plus mr-1"></i> Add Row
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button wire:target="updateProduct" type="submit" class="inline-block focus:outline-none text-blue-500 hover:bg-blue-500 hover:text-white bg-transparent border border-blue-200 dark:bg-transparent dark:text-blue-500 dark:hover:text-white dark:border-blue-700 dark:hover:bg-blue-500  text-sm font-medium py-1 px-3 rounded mb-1">Update</button>
                <button wire:click="$set('showEditModal', false)" type="button" class="inline-block focus:outline-none text-red-500 hover:bg-red-500 hover:text-white bg-transparent border border-gray-200 dark:bg-transparent dark:text-red-500 dark:hover:text-white dark:border-gray-700 dark:hover:bg-red-500  text-sm font-medium py-1 px-3 rounded mb-1">Cancel</button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showBulkStatusModal" title="Change Status for Selected Products" max-width="md">
        <form id="bulkStatusForm" class="space-y-4" wire:submit="bulkChangeStatusProduct">

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
                <button wire:target="bulkChangeStatusProduct" type="submit" class="inline-block focus:outline-none text-yellow-600 hover:bg-yellow-500 hover:text-white bg-transparent border border-yellow-400 dark:border-yellow-600 text-sm font-medium py-1 px-3 rounded mb-1 transition-colors">
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
                    Livewire.dispatch('pg:eventRefresh-productTable');
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
