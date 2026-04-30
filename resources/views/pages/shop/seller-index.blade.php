<div class="relative overflow-hidden px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-gradient-to-b from-[#F6EBD9] via-[#FCF9F4] to-transparent dark:from-gray-900 dark:via-gray-950"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -top-8 right-0 -z-10 h-56 w-56 rounded-full bg-[#D32F2F]/8 blur-3xl dark:bg-[#D32F2F]/10"></div>
    <div aria-hidden="true" class="pointer-events-none absolute left-0 top-32 -z-10 h-56 w-56 rounded-full bg-indigo-500/8 blur-3xl dark:bg-indigo-400/10"></div>

    <div class="mx-auto max-w-7xl space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-5 shadow-[0_24px_80px_-40px_rgba(0,0,0,0.35)] sm:p-6 lg:p-8 dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
            <div class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end">
                <div class="space-y-4">
                    <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] shadow-sm dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D32F2F]"></span>
                        Browse sellers
                    </p>

                    <div class="space-y-2">
                        <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-gray-950 sm:text-4xl dark:text-white">All listings sold by sellers</h1>
                        <p class="max-w-2xl text-sm leading-6 text-gray-600 sm:text-base dark:text-gray-400">Browse every active seller listing in one flat catalog, with direct links to product details.</p>
                    </div>

                    <div class="flex flex-col gap-3 rounded-[1.6rem] border border-black/8 bg-white/90 p-3 shadow-[0_24px_60px_-42px_rgba(0,0,0,0.45)] backdrop-blur sm:flex-row dark:border-white/10 dark:bg-gray-950/80">
                        <label class="flex-1">
                            <span class="sr-only">Search seller listings</span>
                            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search listing, product, seller..." class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                        </label>

                        <select wire:model.live="sortBy" class="rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                            <option value="newest">Newest first</option>
                            <option value="price_asc">Price: low to high</option>
                            <option value="price_desc">Price: high to low</option>
                        </select>

                        <button type="button" wire:click="clearFilters" class="inline-flex items-center justify-center rounded-2xl bg-black px-5 py-3 text-sm font-medium text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                            Clear
                        </button>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Seller listings</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($availableListings) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Active listings from sellers</p>
                    </div>

                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Search</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $search === '' ? 'All' : 'Filtered' }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Find sellers, listings, or products</p>
                    </div>

                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Sort</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ ucfirst(str_replace('_', ' ', $sortBy)) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Quick ordering</p>
                    </div>
                </div>
            </section>

        <section class="space-y-4">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 2xl:grid-cols-4 xl:grid-cols-3">
                @foreach($listings as $listing)
                    @php
                        $product = $listing->variant?->product;
                        $title = data_get($listing, 'display_name') ?: ($product?->name ?? 'Untitled listing');
                        $imageUrl = $product?->image_thumbnail_path ? \App\Utilities\StorageUtility::getUrl($product->image_thumbnail_path) : null;
                        $sellerName = $listing->seller?->shop_name ?: $listing->seller?->user?->username ?: 'Seller';
                        $categories = $product?->display_categories ?? collect();
                    @endphp

                    <a href="{{ route('app.products.show', ['product' => $product?->slug, 'listing' => $listing->slug]) }}" wire:navigate.hover class="group overflow-hidden rounded-[1.75rem] border border-black/8 bg-white/95 shadow-[0_24px_60px_-38px_rgba(0,0,0,0.45)] transition-all duration-300 hover:-translate-y-1.5 hover:border-[#D32F2F]/15 hover:shadow-[0_32px_80px_-38px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900/95">
                        <div class="relative aspect-[5/4] overflow-hidden bg-gray-100 dark:bg-gray-800">
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $title }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110">
                            @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-[#D32F2F] text-white">
                                    <i class="fa-solid fa-gamepad text-3xl opacity-70"></i>
                                </div>
                            @endif

                            <div class="absolute right-3 top-3 rounded-full bg-white/95 px-3.5 py-1.5 text-sm font-semibold text-gray-900 shadow-lg shadow-black/10 dark:bg-gray-950/95 dark:text-white">
                                {{ number_format((float) $listing->price, 0, ',', '.') }} VND
                            </div>
                        </div>

                        <div class="p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#D32F2F] dark:text-[#ff9c9c]">{{ $sellerName }}</p>
                            <h3 class="mt-1 line-clamp-2 text-base font-semibold text-gray-950 dark:text-white">{{ $title }}</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $product?->name ?? 'Unknown product' }}</p>

                            <div class="mt-4 grid grid-cols-2 gap-2 text-[11px]">
                                <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                    <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Stock</div>
                                    <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->stock_count > 0 ? $listing->stock_count.' keys' : 'Sold out' }}</div>
                                </div>
                                <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                    <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Type</div>
                                    <div class="mt-1 font-medium text-gray-900 dark:text-white">Seller listing</div>
                                </div>
                            </div>

                            @if($categories->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($categories->take(2) as $category)
                                        <span class="rounded-full bg-[#FCF9F4] px-2.5 py-1 text-[10px] font-medium text-gray-700 dark:bg-white/5 dark:text-gray-300">{{ $category->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="rounded-[1.75rem] border border-black/8 bg-white/85 px-4 py-3 shadow-[0_24px_60px_-40px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/80">
                {{ $listings->links() }}
            </div>
        </section>
    </div>
</div>
