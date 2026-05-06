<div class="space-y-6">
    @if(session('seller-status'))
        <div class="rounded-[1.75rem] border border-emerald-500/15 bg-emerald-500/8 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
            {{ session('seller-status') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3">
                <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-[#FCF9F4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                    Listing người bán
                </p>
                <div>
                    <h2 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">Quản lý listing và key</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">Tạo listing từ biến thể có sẵn.</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" wire:click="openCreateListingModal('existing_variant')" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                        Tạo listing
                    </button>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Listing</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['listings']) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tất cả listing của người bán</p>
                </div>

                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Đang bán</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['activeListings']) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Listing đã xuất bản</p>
                </div>

                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Đang chờ</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['pendingListings']) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Chờ xử lý</p>
                </div>

                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Key</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['availableKeys']) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tồn kho khả dụng</p>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[2rem] border border-black/8 bg-white p-4 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85 sm:p-6">
        <livewire:shop.seller.table.seller-listings-table />
    </section>

    <x-reusable.modal wire:model="showListingModal" title="{{ $editingListingId ? 'Sửa listing' : 'Tạo listing' }}" max-width="4xl">
        <form class="space-y-6" wire:submit="saveListing">
            @if($editingListingId === null)
                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Cách tạo <span class="text-red-400">*</span></label>
                    <select wire:model.live="createMode" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                        <option value="existing_variant">Dùng biến thể có sẵn</option>
                        <option value="existing_product_variant">Tạo biến thể từ sản phẩm có sẵn</option>
                        <option value="new_product">Tạo sản phẩm mới</option>
                    </select>
                    @error('createMode')
                        <small class="error text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>

                @if($createMode === 'existing_variant')
                    <div>
                        <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Biến thể <span class="text-red-400">*</span></label>
                        <select wire:model="selectedVariantId" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                            <option value="">-- Chọn biến thể --</option>
                            @foreach($this->variantOptions as $variant)
                                <option value="{{ $variant['id'] }}">{{ $variant['label'] }}</option>
                            @endforeach
                        </select>
                        @error('selectedVariantId')
                            <small class="error text-red-500 text-xs">{{ $message }}</small>
                        @enderror
                    </div>
                @elseif($createMode === 'existing_product_variant')
                    <div>
                        <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Sản phẩm <span class="text-red-400">*</span></label>
                        <select wire:model="selectedProductId" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                            <option value="">-- Chọn sản phẩm --</option>
                            @foreach($this->productOptions as $product)
                                <option value="{{ $product['id'] }}">{{ $product['label'] }} · {{ $product['source'] }}</option>
                            @endforeach
                        </select>
                        @error('selectedProductId')
                            <small class="error text-red-500 text-xs">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Khu vực <span class="text-red-400">*</span></label>
                            <select wire:model="variantForm.region_id" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                                <option value="">-- Chọn khu vực --</option>
                                @foreach($this->regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                            @error('variantForm.region_id')
                                <small class="error text-red-500 text-xs">{{ $message }}</small>
                            @enderror
                        </div>
                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Nền tảng <span class="text-red-400">*</span></label>
                            <select wire:model="variantForm.platform_id" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                                <option value="">-- Chọn nền tảng --</option>
                                @foreach($this->platforms as $platform)
                                    <option value="{{ $platform->id }}">{{ $platform->name }}</option>
                                @endforeach
                            </select>
                            @error('variantForm.platform_id')
                                <small class="error text-red-500 text-xs">{{ $message }}</small>
                            @enderror
                        </div>
                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">OS <span class="text-red-400">*</span></label>
                            <select wire:model="variantForm.os_id" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                                <option value="">-- Chọn hệ điều hành --</option>
                                @foreach($this->operatingSystems as $os)
                                    <option value="{{ $os->id }}">{{ $os->name }}</option>
                                @endforeach
                            </select>
                            @error('variantForm.os_id')
                                <small class="error text-red-500 text-xs">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Phiên bản</label>
                        <input wire:model="variantForm.edition" type="text" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" placeholder="Ví dụ: Bản Standard">
                        @error('variantForm.edition')
                            <small class="error text-red-500 text-xs">{{ $message }}</small>
                        @enderror
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Tên <span class="text-red-400">*</span></label>
                            <input wire:model="productForm.name" type="text" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" placeholder="Tên sản phẩm">
                            @error('productForm.name')
                                <small class="error text-red-500 text-xs">{{ $message }}</small>
                            @enderror
                        </div>

                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Slug</label>
                            <input wire:model="productForm.slug" type="text" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" placeholder="optional-slug">
                            @error('productForm.slug')
                                <small class="error text-red-500 text-xs">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Nhà phát hành</label>
                            <input wire:model="productForm.publisher" type="text" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                        </div>

                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Nhà phát triển</label>
                            <input wire:model="productForm.developer" type="text" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Ngày phát hành</label>
                            <input wire:model="productForm.release_date" type="date" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                        </div>

                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Thumbnail</label>
                            <input wire:model="productForm.image" type="file" accept="image/*" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                            @error('productForm.image')
                                <small class="error text-red-500 text-xs">{{ $message }}</small>
                            @enderror
                            @if($productForm->image)
                                <div class="mt-3 overflow-hidden rounded-2xl border border-black/8 bg-white p-2 shadow-sm dark:border-white/10 dark:bg-white/5">
                                    <img src="{{ $productForm->image->temporaryUrl() }}" alt="Thumbnail preview" class="h-40 w-full rounded-xl object-cover">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Mô tả</label>
                        <textarea wire:model="productForm.description" rows="4" class="form-textarea w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500"></textarea>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Khu vực <span class="text-red-400">*</span></label>
                            <select wire:model="variantForm.region_id" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                                <option value="">-- Chọn khu vực --</option>
                                @foreach($this->regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Nền tảng <span class="text-red-400">*</span></label>
                            <select wire:model="variantForm.platform_id" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                                <option value="">-- Chọn nền tảng --</option>
                                @foreach($this->platforms as $platform)
                                    <option value="{{ $platform->id }}">{{ $platform->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">OS <span class="text-red-400">*</span></label>
                            <select wire:model="variantForm.os_id" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500">
                                <option value="">-- Chọn HĐH --</option>
                                @foreach($this->operatingSystems as $os)
                                    <option value="{{ $os->id }}">{{ $os->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Phiên bản</label>
                            <input wire:model="variantForm.edition" type="text" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" placeholder="Ví dụ: Bản Standard">
                        </div>
                    </div>
                @endif
            @endif

            <div>
                 <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Tiêu đề listing</label>
                 <input wire:model="listingForm.display_name" type="text" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" placeholder="Tên hiển thị tùy chọn">
                @error('listingForm.display_name')
                    <small class="error text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-1">
                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Giá <span class="text-red-400">*</span></label>
                    <input wire:model="listingForm.price" type="number" min="0" step="0.01" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500" placeholder="0">
                    @error('listingForm.price')
                        <small class="error text-red-500 text-xs">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4 dark:border-slate-700">
                <button type="submit" class="inline-block rounded-lg border border-blue-200 bg-transparent px-4 py-2 text-sm font-medium text-blue-500 transition-colors hover:bg-blue-500 hover:text-white dark:border-blue-700 dark:text-blue-500 dark:hover:bg-blue-500 dark:hover:text-white">
                     {{ $editingListingId ? 'Cập nhật listing' : 'Tạo listing' }}
                </button>
                <button type="button" wire:click="$set('showListingModal', false)" class="inline-block rounded-lg border border-gray-200 bg-transparent px-4 py-2 text-sm font-medium text-red-500 transition-colors hover:bg-red-500 hover:text-white dark:border-gray-700 dark:text-red-500 dark:hover:bg-red-500 dark:hover:text-white">
                     Hủy
                </button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showKeysModal" title="Quản lý key" max-width="3xl">
        <div class="space-y-5">
            <div>
                <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->viewingListing?->display_name ?: ($this->viewingListing?->variant?->product?->name ?? 'Listing chưa có tên') }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Thêm hoặc xóa key khả dụng cho listing này.</p>
            </div>

            <form class="flex flex-col gap-3 sm:flex-row" wire:submit.prevent="saveKey">
                <input wire:model="keyCode" type="text" class="form-input flex-1 rounded-md border border-slate-300/60 bg-transparent px-3 py-2 dark:border-slate-700 dark:text-slate-300" placeholder="Dán mã key tại đây">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                    Thêm key
                </button>
            </form>

            <div class="overflow-hidden rounded-2xl border border-black/8 dark:border-white/10">
                <table class="min-w-full divide-y divide-black/8 text-sm dark:divide-white/10">
                    <thead class="bg-[#FCF9F4] dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Key</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Trạng thái</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Ngày tạo</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/8 bg-white dark:divide-white/10 dark:bg-gray-950">
                        @forelse($this->viewingListingKeys as $key)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $key->key_code }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $key->status->label() }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $key->created_at?->format('d/m/Y H:i') ?? '--' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if($key->status === \App\Enums\ProductKeyStatus::Available)
                                        <button type="button" wire:click="deleteKey({{ $key->id }})" class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 transition-colors hover:bg-red-500/10 dark:text-red-300">
                                             Xóa
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                 <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Chưa có key.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4 dark:border-slate-700">
                <button type="button" wire:click="$set('showKeysModal', false)" class="inline-block rounded-lg border border-gray-200 bg-transparent px-4 py-2 text-sm font-medium text-red-500 transition-colors hover:bg-red-500 hover:text-white dark:border-gray-700 dark:text-red-500 dark:hover:bg-red-500 dark:hover:text-white">
                     Đóng
                </button>
            </div>
        </div>
    </x-reusable.modal>

    <x-admin.swal-listener table-name="sellerListingsTable" />
</div>
