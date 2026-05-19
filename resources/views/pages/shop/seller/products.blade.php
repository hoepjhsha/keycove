<div class="space-y-6">
    <section class="overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3">
                <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-[#FCF9F4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                    Sản phẩm của tôi
                </p>
                <div>
                    <h2 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">Quản lý product và variant</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Tạo product mới, cập nhật khi còn chờ duyệt, và thêm variant cho product của bạn.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" wire:click="openCreateProductModal" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                        Tạo sản phẩm
                    </button>
                    <a href="{{ route('seller.listings.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                        Quản lý listing
                    </a>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Product</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['products']) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tất cả product của bạn</p>
                </div>

                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Chờ duyệt</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['pendingProducts']) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Có thể chỉnh sửa</p>
                </div>

                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Đã duyệt</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['activeProducts']) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Bị khóa sửa</p>
                </div>

                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Variant</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['variants']) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tất cả biến thể</p>
                </div>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        @forelse($this->products as $product)
            @php
                $productImageUrl = $this->productImageUrl($product->image_thumbnail_path);
                $productStatusClass = match ($product->status) {
                    \App\Enums\GeneralStatus::Active => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                    \App\Enums\GeneralStatus::Inactive => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                    \App\Enums\GeneralStatus::Deleted => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                    default => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
                };
            @endphp

            <article wire:key="product-{{ $product->id }}" class="overflow-hidden rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex min-w-0 gap-4">
                        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-3xl border border-black/8 bg-slate-100 dark:border-white/10 dark:bg-white/5">
                            @if($productImageUrl)
                                <img src="{{ $productImageUrl }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-gray-400 dark:text-gray-600">
                                    <i class="fa-solid fa-box text-xl"></i>
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="truncate text-xl font-semibold text-gray-950 dark:text-white">{{ $product->name }}</h3>
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $productStatusClass }}">{{ $product->status->label() }}</span>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400">Slug: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $product->slug }}</span></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->variants->count() }} variant · {{ $product->listings_count }} listing</p>

                            <div class="flex flex-wrap gap-2 pt-1">
                                @if(! $product->trashed() && $product->status === \App\Enums\GeneralStatus::Inactive)
                                    <button type="button" wire:click="openEditProductModal({{ $product->id }})" class="inline-flex items-center rounded-2xl border border-black/10 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                                        Sửa product
                                    </button>
                                @endif

                                @if($product->trashed())
                                    <button type="button" wire:click="restoreProduct({{ $product->id }})" class="inline-flex items-center rounded-2xl px-3 py-2 text-sm font-semibold text-amber-700 transition-colors hover:bg-amber-500/10 dark:text-amber-300">
                                        Khôi phục product
                                    </button>
                                @else
                                    <button type="button" wire:click="deleteProduct({{ $product->id }})" class="inline-flex items-center rounded-2xl px-3 py-2 text-sm font-semibold text-red-600 transition-colors hover:bg-red-500/10 dark:text-red-300">
                                        Xóa product
                                    </button>
                                @endif

                                @if(! $product->trashed())
                                    <button type="button" wire:click="openVariantModal({{ $product->id }})" class="inline-flex items-center rounded-2xl bg-black px-3 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                        Thêm variant
                                    </button>
                                @endif

                                <a href="{{ route('seller.listings.index') }}" class="inline-flex items-center rounded-2xl border border-black/10 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                                    Sang listing
                                </a>
                            </div>

                            @if($product->status !== \App\Enums\GeneralStatus::Inactive)
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Sản phẩm đã duyệt không thể chỉnh sửa.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-black/8 dark:border-white/10">
                    <div class="grid grid-cols-12 gap-3 border-b border-black/8 bg-[#FCF9F4] px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                        <div class="col-span-5">Biến thể</div>
                        <div class="col-span-2 text-right">Trạng thái</div>
                        <div class="col-span-2 text-right">Listing</div>
                        <div class="col-span-3 text-right">Thông tin</div>
                    </div>

                    <div class="divide-y divide-black/8 dark:divide-white/10">
                        @forelse($product->variants as $variant)
                            @php
                                $variantStatusClass = match ($variant->status) {
                                    \App\Enums\ProductVariantStatus::Draft => 'bg-blue-500/10 text-blue-700 dark:text-blue-300',
                                    \App\Enums\ProductVariantStatus::Active => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                                    \App\Enums\ProductVariantStatus::Hidden => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
                                    \App\Enums\ProductVariantStatus::Deleted => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                                    default => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                                };
                            @endphp

                            <div wire:key="product-{{ $product->id }}-variant-{{ $variant->id }}" class="grid grid-cols-12 gap-3 px-4 py-4 text-sm">
                                <div class="col-span-5 min-w-0">
                                    <p class="truncate font-semibold text-gray-950 dark:text-white">{{ collect([$variant->region?->name, $variant->platform?->name, $variant->operatingSystem?->name, $variant->edition])->filter()->implode(' · ') }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">ID #{{ $variant->id }}</p>
                                </div>
                                <div class="col-span-2 flex justify-end">
                                    <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $variantStatusClass }}">{{ $variant->status->label() }}</span>
                                </div>
                                <div class="col-span-2 text-right font-semibold text-gray-950 dark:text-white">{{ number_format($variant->listings_count) }}</div>
                                <div class="col-span-3 text-right text-gray-600 dark:text-gray-300">{{ $variant->region?->name ?? '--' }} · {{ $variant->platform?->name ?? '--' }} · {{ $variant->operatingSystem?->name ?? '--' }}</div>
                                <div class="col-span-12 mt-3 flex justify-end">
                                    <div class="flex flex-wrap gap-2">
                                        @if($variant->trashed())
                                            <button type="button" wire:click="restoreVariant({{ $variant->id }})" class="inline-flex items-center rounded-2xl px-3 py-2 text-xs font-semibold text-amber-700 transition-colors hover:bg-amber-500/10 dark:text-amber-300">
                                                Khôi phục variant
                                            </button>
                                        @else
                                            @if($product->status === \App\Enums\GeneralStatus::Active && in_array($variant->status, [\App\Enums\ProductVariantStatus::Active, \App\Enums\ProductVariantStatus::Hidden], true))
                                                <button type="button" wire:click="toggleVariantStatus({{ $variant->id }})" class="inline-flex items-center rounded-2xl px-3 py-2 text-xs font-semibold text-indigo-600 transition-colors hover:bg-indigo-500/10 dark:text-indigo-300">
                                                    {{ $variant->status === \App\Enums\ProductVariantStatus::Active ? 'Ẩn variant' : 'Kích hoạt variant' }}
                                                </button>
                                            @endif

                                            <button type="button" wire:click="openEditVariantModal({{ $variant->id }})" class="inline-flex items-center rounded-2xl px-3 py-2 text-xs font-semibold text-blue-600 transition-colors hover:bg-blue-500/10 dark:text-blue-300">
                                                Sửa variant
                                            </button>

                                            <button type="button" wire:click="deleteVariant({{ $variant->id }})" class="inline-flex items-center rounded-2xl px-3 py-2 text-xs font-semibold text-red-600 transition-colors hover:bg-red-500/10 dark:text-red-300">
                                                Xóa variant
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">
                                Chưa có variant nào.
                            </div>
                        @endforelse
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-[2rem] border border-dashed border-black/15 bg-white px-6 py-10 text-center dark:border-white/10 dark:bg-gray-900/85">
                <p class="text-lg font-semibold text-gray-950 dark:text-white">Chưa có product nào</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tạo product đầu tiên để bắt đầu quản lý variant.</p>
                <button type="button" wire:click="openCreateProductModal" class="mt-4 inline-flex items-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                    Tạo sản phẩm
                </button>
            </div>
        @endforelse
    </section>

    <x-reusable.modal wire:model="showProductModal" title="{{ $editingProductId ? 'Sửa sản phẩm' : 'Tạo sản phẩm' }}" max-width="4xl">
        <form class="space-y-6" wire:submit="saveProduct">
            @if($editingProductId === null)
                <div class="rounded-2xl border border-amber-500/15 bg-amber-500/8 px-4 py-3 text-sm text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
                    Sản phẩm mới sẽ ở trạng thái chờ duyệt cho đến khi admin duyệt.
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Tên <span class="text-red-400">*</span></label>
                    <input wire:model="{{ $editingProductId ? 'editForm.name' : 'createForm.name' }}" type="text" class="form-input mt-1 w-full rounded-md border border-slate-300/60 bg-transparent px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-slate-700 dark:text-slate-300" placeholder="Tên sản phẩm">
                    @error($editingProductId ? 'editForm.name' : 'createForm.name')
                        <small class="error text-xs text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Slug</label>
                    <input wire:model="{{ $editingProductId ? 'editForm.slug' : 'createForm.slug' }}" type="text" class="form-input mt-1 w-full rounded-md border border-slate-300/60 bg-transparent px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-slate-700 dark:text-slate-300" placeholder="optional-slug">
                    @error($editingProductId ? 'editForm.slug' : 'createForm.slug')
                        <small class="error text-xs text-red-500">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Nhà phát hành</label>
                    <input wire:model="{{ $editingProductId ? 'editForm.publisher' : 'createForm.publisher' }}" type="text" class="form-input mt-1 w-full rounded-md border border-slate-300/60 bg-transparent px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-slate-700 dark:text-slate-300">
                </div>

                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Nhà phát triển</label>
                    <input wire:model="{{ $editingProductId ? 'editForm.developer' : 'createForm.developer' }}" type="text" class="form-input mt-1 w-full rounded-md border border-slate-300/60 bg-transparent px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-slate-700 dark:text-slate-300">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Ngày phát hành</label>
                    <input wire:model="{{ $editingProductId ? 'editForm.release_date' : 'createForm.release_date' }}" type="date" class="form-input mt-1 w-full rounded-md border border-slate-300/60 bg-transparent px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-slate-700 dark:text-slate-300">
                </div>

                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Thumbnail</label>
                    <input wire:model="{{ $editingProductId ? 'editForm.image' : 'createForm.image' }}" type="file" accept="image/*" class="form-input mt-1 w-full rounded-md border border-slate-300/60 bg-transparent px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-slate-700 dark:text-slate-300">
                    @error($editingProductId ? 'editForm.image' : 'createForm.image')
                        <small class="error text-xs text-red-500">{{ $message }}</small>
                    @enderror

                    @if($editingProductId && $editForm->image)
                        <div class="mt-3 overflow-hidden rounded-2xl border border-black/8 bg-white p-2 shadow-sm dark:border-white/10 dark:bg-white/5">
                            <img src="{{ $editForm->image->temporaryUrl() }}" alt="Thumbnail preview" class="h-40 w-full rounded-xl object-cover">
                        </div>
                    @elseif($editingProductId && $editForm->product?->image_thumbnail_path)
                        <div class="mt-3 overflow-hidden rounded-2xl border border-black/8 bg-white p-2 shadow-sm dark:border-white/10 dark:bg-white/5">
                            <img src="{{ $this->productImageUrl($editForm->product->image_thumbnail_path) }}" alt="Thumbnail hiện tại" class="h-40 w-full rounded-xl object-cover">
                        </div>
                    @elseif(!$editingProductId && $createForm->image)
                        <div class="mt-3 overflow-hidden rounded-2xl border border-black/8 bg-white p-2 shadow-sm dark:border-white/10 dark:bg-white/5">
                            <img src="{{ $createForm->image->temporaryUrl() }}" alt="Thumbnail preview" class="h-40 w-full rounded-xl object-cover">
                        </div>
                    @endif
                </div>
            </div>

            <div>
                <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Mô tả</label>
                <textarea wire:model="{{ $editingProductId ? 'editForm.description' : 'createForm.description' }}" rows="4" class="form-textarea mt-1 w-full rounded-md border border-slate-300/60 bg-transparent px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-slate-700 dark:text-slate-300"></textarea>
            </div>

            @error('product')
                <div class="rounded-2xl border border-rose-500/15 bg-rose-500/8 px-4 py-3 text-sm text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200">
                    {{ $message }}
                </div>
            @enderror

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4 dark:border-slate-700">
                <button type="submit" class="inline-block rounded-lg border border-blue-200 bg-transparent px-4 py-2 text-sm font-medium text-blue-500 transition-colors hover:bg-blue-500 hover:text-white dark:border-blue-700 dark:text-blue-500 dark:hover:bg-blue-500 dark:hover:text-white">
                    {{ $editingProductId ? 'Cập nhật sản phẩm' : 'Tạo sản phẩm' }}
                </button>
                <button type="button" wire:click="$set('showProductModal', false)" class="inline-block rounded-lg border border-gray-200 bg-transparent px-4 py-2 text-sm font-medium text-red-500 transition-colors hover:bg-red-500 hover:text-white dark:border-gray-700 dark:text-red-500 dark:hover:bg-red-500 dark:hover:text-white">
                    Hủy
                </button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showVariantModal" title="{{ $editingVariantId ? 'Sửa variant' : 'Thêm variant' }}" max-width="3xl">
        <form class="space-y-6" wire:submit="saveVariant">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Khu vực <span class="text-red-400">*</span></label>
                    <select wire:model="variantForm.region_id" class="form-select mt-1 w-full rounded-md border border-slate-300/60 bg-transparent px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-slate-700 dark:text-slate-300">
                        <option value="">-- Chọn khu vực --</option>
                        @foreach($this->regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                        @endforeach
                    </select>
                    @error('variantForm.region_id')
                        <small class="error text-xs text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Nền tảng <span class="text-red-400">*</span></label>
                    <select wire:model="variantForm.platform_id" class="form-select mt-1 w-full rounded-md border border-slate-300/60 bg-transparent px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-slate-700 dark:text-slate-300">
                        <option value="">-- Chọn nền tảng --</option>
                        @foreach($this->platforms as $platform)
                            <option value="{{ $platform->id }}">{{ $platform->name }}</option>
                        @endforeach
                    </select>
                    @error('variantForm.platform_id')
                        <small class="error text-xs text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="font-medium text-sm text-slate-600 dark:text-slate-400">OS <span class="text-red-400">*</span></label>
                    <select wire:model="variantForm.os_id" class="form-select mt-1 w-full rounded-md border border-slate-300/60 bg-transparent px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-slate-700 dark:text-slate-300">
                        <option value="">-- Chọn hệ điều hành --</option>
                        @foreach($this->operatingSystems as $os)
                            <option value="{{ $os->id }}">{{ $os->name }}</option>
                        @endforeach
                    </select>
                    @error('variantForm.os_id')
                        <small class="error text-xs text-red-500">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div>
                <label class="font-medium text-sm text-slate-600 dark:text-slate-400">Phiên bản</label>
                <input wire:model="variantForm.edition" type="text" class="form-input mt-1 w-full rounded-md border border-slate-300/60 bg-transparent px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-slate-700 dark:text-slate-300" placeholder="Ví dụ: Standard Edition">
                @error('variantForm.edition')
                    <small class="error text-xs text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4 dark:border-slate-700">
                <button type="submit" class="inline-block rounded-lg border border-blue-200 bg-transparent px-4 py-2 text-sm font-medium text-blue-500 transition-colors hover:bg-blue-500 hover:text-white dark:border-blue-700 dark:text-blue-500 dark:hover:bg-blue-500 dark:hover:text-white">
                    {{ $editingVariantId ? 'Cập nhật variant' : 'Thêm variant' }}
                </button>
                <button type="button" wire:click="$set('showVariantModal', false)" class="inline-block rounded-lg border border-gray-200 bg-transparent px-4 py-2 text-sm font-medium text-red-500 transition-colors hover:bg-red-500 hover:text-white dark:border-gray-700 dark:text-red-500 dark:hover:bg-red-500 dark:hover:text-white">
                    Hủy
                </button>
            </div>
        </form>
    </x-reusable.modal>

    <x-admin.swal-listener />
</div>
