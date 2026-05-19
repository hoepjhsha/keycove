<div>
    @section('pageTitle', __('admin.titles.product_details') . ': ' . $product->name)

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => __('admin.nav.catalog_keys'), 'url' => 'javascript:void(0)'],
            ['label' => 'Products List', 'url' => \Illuminate\Support\Facades\Route::has('admin.products.index') ? route('admin.products.index') : url('/admin/products')],
            ['label' => $product->name, 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 shadow rounded-md w-full relative">
            <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
                <h4 class="font-medium">{{ __('admin.common.product') }}</h4>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <div class="lg:col-span-1">
                        @if($product->image_thumbnail_path)
                            <div class="bg-slate-100 dark:bg-slate-700 rounded-lg p-4 flex items-center justify-center min-h-[200px]">
                                <img src="{{ StorageUtility::getUrl($product->image_thumbnail_path) }}" alt="{{ $product->name }}" class="max-w-full max-h-[200px] object-contain rounded">
                            </div>
                        @else
                            <div class="bg-slate-100 dark:bg-slate-700 rounded-lg p-4 flex items-center justify-center min-h-[200px]">
                                <div class="text-center">
                                    <i class="fa-solid fa-image text-4xl text-slate-300 dark:text-slate-600 mb-2"></i>
                                    <p class="text-slate-500 dark:text-slate-400 text-xs">{{ __('admin.common.no_image') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="lg:col-span-3 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('admin.common.name') }}</p>
                                <p class="text-slate-900 dark:text-white font-medium">{{ $product->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('admin.common.slug') }}</p>
                                <p class="text-slate-900 dark:text-white">{{ $product->slug }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('admin.common.publisher') }}</p>
                                <p class="text-slate-900 dark:text-white">{{ $product->publisher ?? '--N/A--' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('admin.common.developer') }}</p>
                                <p class="text-slate-900 dark:text-white">{{ $product->developer ?? '--N/A--' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('admin.common.release_date_full') }}</p>
                                <p class="text-slate-900 dark:text-white">{{ $product->release_date?->format('d/m/Y') ?? '--N/A--' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('admin.common.submitted_by') }}</p>
                                <p class="text-slate-900 dark:text-white">{{ $product->submittedBySeller?->shop_name ?? 'Shop Admin' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('admin.common.status') }}</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->status->value === 1 ? 'bg-green-500/10 text-green-500' : ($product->status->value === 2 ? 'bg-yellow-500/10 text-yellow-500' : 'bg-gray-500/10 text-gray-500') }}">
                                    {{ $product->status->label() }}
                                </span>
                            </div>
                        </div>

                        @if($product->categories->isNotEmpty())
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">{{ __('admin.nav.categories') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($product->categories as $category)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($product->description)
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">{{ __('admin.common.description') }}</p>
                                <div class="text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700/30 rounded-lg p-3 max-h-32 overflow-y-auto">
                                    {!! nl2br(e($product->description)) !!}
                                </div>
                            </div>
                        @endif

                        @if($product->system_requirement)
                            <div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">{{ __('admin.common.system_requirements') }}</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($product->system_requirement as $key => $value)
                                        <div class="bg-slate-50 dark:bg-slate-700/30 rounded-lg p-2">
                                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                            <p class="text-sm text-slate-900 dark:text-white font-medium mt-1">{{ $value }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow rounded-md w-full relative">
            <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 flex items-center justify-between">
                <h4 class="font-medium dark:text-slate-300">Biến thể ({{ $this->productVariants->count() }})</h4>
                <div class="flex items-center gap-3">
                    <button wire:click="toggleShowTrashedVariants" class="inline-flex items-center px-3 py-1.5 text-sm font-medium {{ $showTrashedVariants ? 'text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }} rounded-md transition-colors" title="{{ $showTrashedVariants ? 'Ẩn đã xóa' : 'Hiện đã xóa' }}">
                        <i class="fa-solid {{ $showTrashedVariants ? 'fa-eye-slash' : 'fa-eye' }} mr-1.5"></i>
                        {{ $showTrashedVariants ? 'Ẩn đã xóa' : 'Hiện đã xóa' }}
                    </button>
                    <button wire:click="openVariantModal()" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-plus mr-1.5"></i> Tạo
                    </button>
                </div>
            </div>
            <div class="flex-auto p-4">
                @if($this->productVariants->isEmpty())
                    <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                        <i class="fa-solid fa-box-open text-4xl mb-3"></i>
                        <p>No variants found. Create your first variant to get started.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700">
                                    <th class="px-3 py-2 text-left font-medium text-slate-600 dark:text-slate-400 w-10"></th>
                                    <th class="px-3 py-2 text-left font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.region') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-slate-600 dark:text-slate-400">{{ __('admin.nav.platforms') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-slate-600 dark:text-slate-400">OS</th>
                                    <th class="px-3 py-2 text-left font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.edition') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.status') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.listings') }}</th>
                                    <th class="px-3 py-2 text-right font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->productVariants as $variant)
                                    <tr class="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 {{ $variant->trashed() ? 'bg-red-50/50 dark:bg-red-950/20 opacity-75' : '' }}">
                                        <td class="px-3 py-2">
                                            <button type="button" wire:click="toggleVariantRow({{ $variant->id }})" class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                @if(in_array($variant->id, $expandedVariantRows))
                                                    <i class="fa-solid fa-chevron-down text-gray-600 dark:text-gray-400"></i>
                                                @else
                                                    <i class="fa-solid fa-chevron-right text-gray-600 dark:text-gray-400"></i>
                                                @endif
                                            </button>
                                        </td>
                                        <td class="px-3 py-2 text-slate-900 dark:text-white">{{ $variant->region->name ?? '-' }}</td>
                                        <td class="px-3 py-2 text-slate-900 dark:text-white">{{ $variant->platform->name ?? '-' }}</td>
                                        <td class="px-3 py-2 text-slate-900 dark:text-white">{{ $variant->operatingSystem->name ?? '-' }}</td>
                                        <td class="px-3 py-2 text-slate-900 dark:text-white">{{ $variant->edition ?? '-' }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $variant->status->value === 1 ? 'bg-green-500/10 text-green-500' : ($variant->status->value === 2 ? 'bg-yellow-500/10 text-yellow-500' : 'bg-gray-500/10 text-gray-500') }}">
                                                {{ $variant->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-slate-900 dark:text-white">{{ $variant->listings_count }}</td>
                                        <td class="px-3 py-2 text-right">
                                            @if($variant->trashed())
                                                <button wire:click="restoreVariant({{ $variant->id }})" class="text-emerald-600 hover:text-emerald-800 px-1 transition-all hover:scale-110" title="Restore">
                                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                                </button>
                                            @else
                                                <button wire:click="openListingModal({{ $variant->id }})" class="text-indigo-600 hover:text-indigo-900 px-1 transition-all hover:scale-110" title="Add Listing">
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>
                                                <button wire:click="openVariantModal({{ $variant->id }})" class="text-blue-600 hover:text-blue-800 px-1 transition-all hover:scale-110" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button wire:click="deleteVariant({{ $variant->id }})" class="text-red-500 hover:text-red-700 px-1 transition-all hover:scale-110" title="Delete">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @if(in_array($variant->id, $expandedVariantRows))
                                        <tr>
                                            <td colspan="8" class="px-3 py-4 bg-slate-50 dark:bg-slate-700/20">
                                                <div class="pl-4 border-l-2 border-blue-500">
                                                    <div class="flex items-center justify-between mb-3">
                                                         <h5 class="text-sm font-medium text-slate-700 dark:text-slate-300">Danh sách listing của biến thể</h5>
                                                         <button wire:click="toggleShowTrashedListings" class="inline-flex items-center px-2 py-1 text-xs font-medium {{ $showTrashedListings ? 'text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }} rounded transition-colors" title="{{ $showTrashedListings ? 'Ẩn đã xóa' : 'Hiện đã xóa' }}">
                                                             <i class="fa-solid {{ $showTrashedListings ? 'fa-eye-slash' : 'fa-eye' }} mr-1"></i>
                                                             {{ $showTrashedListings ? 'Ẩn đã xóa' : 'Hiện đã xóa' }}
                                                         </button>
                                                     </div>
                                                    @php $variantListings = $this->getVariantListings($variant->id); @endphp
                                                    @if($variantListings->isEmpty())
                                                        <p class="text-sm text-slate-500 dark:text-slate-400 italic">Không có listing nào cho biên thể này.</p>
                                                    @else
                                                        <table class="w-full text-xs">
                                                            <thead>
                                                                <tr class="border-b border-slate-200 dark:border-slate-600">
                                                                    <th class="px-2 py-1 text-left text-slate-500 dark:text-slate-400">{{ __('admin.common.seller') }}</th>
                                                                    <th class="px-2 py-1 text-left text-slate-500 dark:text-slate-400">{{ __('admin.common.slug') }}</th>
                                                                    <th class="px-2 py-1 text-left text-slate-500 dark:text-slate-400">{{ __('admin.common.price') }}</th>
                                                                    <th class="px-2 py-1 text-left text-slate-500 dark:text-slate-400">{{ __('admin.common.status') }}</th>
                                                                    <th class="px-2 py-1 text-left text-slate-500 dark:text-slate-400">{{ __('admin.common.available_keys') }}</th>
                                                                    <th class="px-2 py-1 text-right text-slate-500 dark:text-slate-400">{{ __('admin.common.actions') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($variantListings as $listing)
                                                                    <tr class="border-b border-slate-100 dark:border-slate-700/50 {{ $listing->trashed() ? 'bg-red-50/50 dark:bg-red-950/20 opacity-75' : '' }}">
                                        <td class="px-2 py-1 text-slate-700 dark:text-slate-300">{{ $listing->seller?->shop_name ?? 'Shop Admin' }}</td>
                                        <td class="px-2 py-1 text-slate-700 dark:text-slate-300 font-mono text-xs">{{ $listing->slug }}</td>
                                        <td class="px-2 py-1 text-slate-700 dark:text-slate-300">{{ number_format((float) $listing->price, 2) }} VND</td>
                                                                        <td class="px-2 py-1">
                                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $listing->status->value === 2 ? 'bg-green-500/10 text-green-500' : ($listing->status->value === 1 ? 'bg-blue-500/10 text-blue-500' : 'bg-gray-500/10 text-gray-500') }}">
                                                                                {{ $listing->status->label() }}
                                                                            </span>
                                                                        </td>
                                                                        <td class="px-2 py-1 text-slate-700 dark:text-slate-300">{{ $listing->keys_count }}</td>
                                                                        <td class="px-2 py-1 text-right">
                                                                            @if($listing->trashed())
                                                                                <button wire:click="restoreListing({{ $listing->id }})" class="text-emerald-600 hover:text-emerald-800 px-1" title="Restore">
                                                                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                                                                </button>
                                                                            @else
                                                                                <button wire:click="openKeysModal({{ $listing->id }})" class="text-indigo-600 hover:text-indigo-900 px-1" title="View Keys">
                                                                                    <i class="fa-solid fa-key"></i>
                                                                                </button>
                                                                                <button wire:click="openListingModal({{ $variant->id }}, {{ $listing->id }})" class="text-blue-600 hover:text-blue-800 px-1" title="Edit">
                                                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                                                </button>
                                                                                <button wire:click="deleteListing({{ $listing->id }})" class="text-red-500 hover:text-red-700 px-1" title="Delete">
                                                                                    <i class="fa-solid fa-trash-can"></i>
                                                                                </button>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-reusable.modal wire:model="showVariantModal" title="{{ $editingVariantId ? 'Sửa biến thể' : 'Tạo biến thể' }}" max-width="lg">
        <form class="space-y-4" wire:submit="saveVariant">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">{{ __('admin.common.region') }} <span class="text-red-400">*</span></label>
                    <select wire:model="variantForm.region_id" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" required>
                        <option value="">-- Chọn vùng --</option>
                        @foreach($this->regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }} ({{ $region->flag_code }})</option>
                        @endforeach
                    </select>
                    @error('variantForm.region_id')
                        <small class="error text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">{{ __('admin.nav.platforms') }} <span class="text-red-400">*</span></label>
                    <select wire:model="variantForm.platform_id" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" required>
                        <option value="">-- Chọn nền tảng --</option>
                        @foreach($this->platforms as $platform)
                            <option value="{{ $platform->id }}">{{ $platform->name }}</option>
                        @endforeach
                    </select>
                    @error('variantForm.platform_id')
                        <small class="error text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">{{ __('admin.nav.operating_systems') }} <span class="text-red-400">*</span></label>
                    <select wire:model="variantForm.os_id" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" required>
                        <option value="">-- Chọn hệ điều hành --</option>
                        @foreach($this->operatingSystems as $os)
                            <option value="{{ $os->id }}">{{ $os->name }}</option>
                        @endforeach
                    </select>
                    @error('variantForm.os_id')
                        <small class="error text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">{{ __('admin.common.edition') }}</label>
                    <input wire:model="variantForm.edition" type="text" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" placeholder="e.g., Standard Edition">
                    @error('variantForm.edition')
                        <small class="error text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div>
                <label class="font-medium text-sm text-slate-600 dark:text-slate-400">{{ __('admin.common.status') }} <span class="text-red-400">*</span></label>
                <select wire:model="variantForm.status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" required>
                    @foreach(\App\Enums\ProductVariantStatus::cases() as $status)
                        @if($status !== \App\Enums\ProductVariantStatus::Deleted)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endif
                    @endforeach
                </select>
                @error('variantForm.status')
                    <small class="error text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button wire:target="saveVariant" type="submit" class="inline-block focus:outline-none text-blue-500 hover:bg-blue-500 hover:text-white bg-transparent border border-blue-200 dark:bg-transparent dark:text-blue-500 dark:hover:text-white dark:border-blue-700 dark:hover:bg-blue-500 text-sm font-medium py-2 px-4 rounded transition-colors">
                    {{ $editingVariantId ? 'Update' : 'Create' }}
                </button>
                <button wire:click="$set('showVariantModal', false)" type="button" class="inline-block focus:outline-none text-red-500 hover:bg-red-500 hover:text-white bg-transparent border border-gray-200 dark:bg-transparent dark:text-red-500 dark:hover:text-white dark:border-gray-700 dark:hover:bg-red-500 text-sm font-medium py-2 px-4 rounded transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showListingModal" title="{{ $editingListingId ? 'Edit Listing' : 'Create Listing' }}" max-width="lg">
        <form class="space-y-4" wire:submit="saveListing">
            <div>
                <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Listing Title <span class="text-slate-400">(Optional)</span></label>
                <input wire:model="listingForm.display_name" type="text" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" placeholder="{{ __('admin.placeholders.listing_display_name') }}">
                @error('listingForm.display_name')
                    <small class="error text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label class="font-medium text-sm text-slate-600 dark:text-slate-400">{{ __('admin.common.seller') }}</label>
                <select wire:model="listingForm.seller_id" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                    <option value="">{{ __('admin.common.shop_admin') }}</option>
                    @foreach($this->sellers as $seller)
                        <option value="{{ $seller->id }}">{{ $seller->shop_name }} ({{ $seller->user->email ?? 'N/A' }})</option>
                    @endforeach
                </select>
                @error('listingForm.seller_id')
                    <small class="error text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label class="font-medium text-sm text-slate-600 dark:text-slate-400">{{ __('admin.common.price') }} <span class="text-red-400">*</span></label>
                <div class="relative mt-1">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-500 dark:text-slate-400">VND</span>
                    <input wire:model="listingForm.price" type="number" step="0.01" min="0" class="form-input w-full rounded-md border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent pl-14 pr-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" placeholder="0.00" required>
                </div>
                @error('listingForm.price')
                    <small class="error text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label class="font-medium text-sm text-slate-600 dark:text-slate-400">{{ __('admin.common.status') }} <span class="text-red-400">*</span></label>
                <select wire:model="listingForm.status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" required>
                    @foreach(\App\Enums\ProductListingStatus::cases() as $status)
                        @if($status === \App\Enums\ProductListingStatus::Deleted)
                            @continue
                        @endif

                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
                @error('listingForm.status')
                    <small class="error text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button wire:target="saveListing" type="submit" class="inline-block focus:outline-none text-blue-500 hover:bg-blue-500 hover:text-white bg-transparent border border-blue-200 dark:bg-transparent dark:text-blue-500 dark:hover:text-white dark:border-blue-700 dark:hover:bg-blue-500 text-sm font-medium py-2 px-4 rounded transition-colors">
                    {{ $editingListingId ? 'Update' : 'Create' }}
                </button>
                <button wire:click="$set('showListingModal', false)" type="button" class="inline-block focus:outline-none text-red-500 hover:bg-red-500 hover:text-white bg-transparent border border-gray-200 dark:bg-transparent dark:text-red-500 dark:hover:text-white dark:border-gray-700 dark:hover:bg-red-500 text-sm font-medium py-2 px-4 rounded transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showKeysModal" :title="__('admin.common.available_keys')" max-width="3xl">
        <div class="space-y-4">
            <form class="flex gap-2 items-end" wire:submit="saveKey">
                <div class="flex-1">
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">{{ __('admin.common.key_code') }} <span class="text-red-400">*</span></label>
                    <input wire:model="keyForm.key_code" type="text" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX" required>
                    @error('keyForm.key_code')
                        <small class="error text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
                <button wire:target="saveKey" type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fa-solid fa-plus mr-1.5"></i> Add Key
                </button>
            </form>

            <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-2 border-b border-slate-200 dark:border-slate-700">
                    <h5 class="text-sm font-medium text-slate-700 dark:text-slate-300">Key khả dụng ({{ $this->viewingListingKeys->where('status', \App\Enums\ProductKeyStatus::Available->value)->count() }} / {{ $this->viewingListingKeys->count() }})</h5>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    @if($this->viewingListingKeys->isEmpty())
                        <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                            <i class="fa-solid fa-key text-2xl mb-2"></i>
                            <p>{{ __('admin.common.no_keys_available') }}</p>
                        </div>
                    @else
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-700/30 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.key_code') }}</th>
                                    <th class="px-4 py-2 text-left font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.status') }}</th>
                                    <th class="px-4 py-2 text-right font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach($this->viewingListingKeys as $key)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                        <td class="px-4 py-2 font-mono text-slate-900 dark:text-white">{{ $key->key_code }}</td>
                                        <td class="px-4 py-2">
                                            @if($key->status === \App\Enums\ProductKeyStatus::Available)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-500">
                                                    Available
                                                </span>
                                            @elseif($key->status === \App\Enums\ProductKeyStatus::Reserved)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-500">
                                                    Reserved
                                                </span>
                                            @elseif($key->status === \App\Enums\ProductKeyStatus::Sold)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-500/10 text-purple-500">
                                                    Sold
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-500">
                                                    {{ $key->status->label() }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            @if($key->status === \App\Enums\ProductKeyStatus::Available && $key->order_item_id === null)
                                                <button wire:click="deleteKey({{ $key->id }})" class="text-red-500 hover:text-red-700 transition-colors" x-tooltip="Delete">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            @else
                                                <span class="text-slate-400 cursor-not-allowed">
                                                    <i class="fa-solid fa-lock"></i>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-end w-full">
                <button wire:click="$set('showKeysModal', false)" type="button" class="inline-block focus:outline-none text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-600 text-sm font-medium py-2 px-4 transition-colors">
                    Close
                </button>
            </div>
        </x-slot:footer>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showVariantBulkStatusModal" :title="__('admin.modal.change_variant_status')" max-width="md">
        <form class="space-y-4" wire:submit="bulkChangeVariantStatus">
            <div>
                <label class="font-medium text-sm text-slate-600 dark:text-slate-400">{{ __('admin.common.select_new_status') }} <span class="text-red-400">*</span></label>
                <select wire:model="variantBulkStatusForm_status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" required>
                    <option value="">{{ __('admin.common.select_status') }}</option>
                    @foreach(\App\Enums\ProductVariantStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
                @error('variantBulkStatusForm_status')
                    <small class="error text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-2 mt-6">
                <button wire:target="bulkChangeVariantStatus" type="submit" class="inline-block focus:outline-none text-yellow-600 hover:bg-yellow-500 hover:text-white bg-transparent border border-yellow-400 dark:border-yellow-600 text-sm font-medium py-2 px-4 rounded mb-1 transition-colors">
                    Apply Status
                </button>
                <button wire:click="$set('showVariantBulkStatusModal', false)" type="button" class="inline-block focus:outline-none text-slate-500 hover:bg-slate-500 hover:text-white bg-transparent border border-slate-300 dark:border-slate-600 text-sm font-medium py-2 px-4 rounded mb-1 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showListingBulkStatusModal" :title="__('admin.modal.change_listing_status')" max-width="md">
        <form class="space-y-4" wire:submit="bulkChangeListingStatus">
            <div>
                <label class="font-medium text-sm text-slate-600 dark:text-slate-400">{{ __('admin.common.select_new_status') }} <span class="text-red-400">*</span></label>
                <select wire:model="listingBulkStatusForm_status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" required>
                    <option value="">{{ __('admin.common.select_status') }}</option>
                    @foreach(\App\Enums\ProductListingStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
                @error('listingBulkStatusForm_status')
                    <small class="error text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-2 mt-6">
                <button wire:target="bulkChangeListingStatus" type="submit" class="inline-block focus:outline-none text-yellow-600 hover:bg-yellow-500 hover:text-white bg-transparent border border-yellow-400 dark:border-yellow-600 text-sm font-medium py-2 px-4 rounded mb-1 transition-colors">
                    Apply Status
                </button>
                <button wire:click="$set('showListingBulkStatusModal', false)" type="button" class="inline-block focus:outline-none text-slate-500 hover:bg-slate-500 hover:text-white bg-transparent border border-slate-300 dark:border-slate-600 text-sm font-medium py-2 px-4 rounded mb-1 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </x-reusable.modal>

    @push('scripts')
        <x-admin.swal-listener table-names="productVariantsTable,productListingsTable" />
    @endpush
</div>
