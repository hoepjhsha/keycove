<div class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div class="mx-auto max-w-7xl space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <nav aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <li>
                            <a href="{{ route('app.products.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <i class="fa-solid fa-house"></i>
                            </a>
                        </li>
                        <li>
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </li>
                        <li class="font-semibold text-gray-800 dark:text-gray-200" aria-current="page">Products</li>
                    </ol>
                </nav>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-400">Shop catalog</p>
                    <h1 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white sm:text-3xl">Browse admin listings</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Search, filter, and sort shop-admin listings in one place.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm">
                <span class="rounded-full bg-gray-100 px-3 py-1 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ number_format($availableListings) }} listings</span>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-[18rem_minmax(0,1fr)] lg:items-start">
            <aside style="position: sticky; top: 1.5rem;"
                   class="lg:sticky lg:top-6 lg:self-start lg:h-fit rounded-md border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Filters</p>
                        <h2 class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">Refine results</h2>
                    </div>

                    <button type="button" wire:click="clearFilters" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                        Clear
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Title, product, edition..." class="mt-1 w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
                        <select wire:model.live="categoryId" class="mt-1 w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                            <option value="0">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ data_get($category, 'id') }}">{{ data_get($category, 'name') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Platform</label>
                            <select wire:model.live="platformId" class="mt-1 w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                                <option value="0">All</option>
                                @foreach($platforms as $platform)
                                    <option value="{{ $platform->id }}">{{ $platform->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Region</label>
                            <select wire:model.live="regionId" class="mt-1 w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                                <option value="0">All</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Operating system</label>
                        <select wire:model.live="osId" class="mt-1 w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                            <option value="0">All operating systems</option>
                            @foreach($operatingSystems as $os)
                                <option value="{{ $os->id }}">{{ $os->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Edition</label>
                        <input wire:model.live.debounce.300ms="edition" type="text" placeholder="Standard, Deluxe..." class="mt-1 w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Min price</label>
                            <input wire:model.live.debounce.300ms="minPrice" type="number" min="0" step="0.01" placeholder="0" class="mt-1 w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Max price</label>
                            <input wire:model.live.debounce.300ms="maxPrice" type="number" min="0" step="0.01" placeholder="0" class="mt-1 w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        </div>
                    </div>

                    <label class="flex items-center gap-3 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200">
                        <input wire:model.live="inStock" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Only in stock
                    </label>

                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Sort</label>
                        <select wire:model.live="sortBy" class="mt-1 w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                            @foreach($sortOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </aside>

            <section class="space-y-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between rounded-md border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Showing <span class="font-semibold text-gray-900 dark:text-white">{{ $listings->count() }}</span> of <span class="font-semibold text-gray-900 dark:text-white">{{ $availableListings }}</span>
                    </p>

                    <div class="flex flex-wrap items-center gap-2">
                        <div class="inline-flex rounded-md border border-gray-200 bg-gray-50 p-1 dark:border-gray-700 dark:bg-gray-950">
                            <button type="button" wire:click="setViewMode('grid')" title="Grid view" aria-label="Grid view" class="inline-flex h-9 w-9 items-center justify-center rounded-sm text-sm transition-colors {{ $viewMode === 'grid' ? 'bg-white text-indigo-600 shadow-sm dark:bg-gray-800 dark:text-indigo-300' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200' }}">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <button type="button" wire:click="setViewMode('list')" title="List view" aria-label="List view" class="inline-flex h-9 w-9 items-center justify-center rounded-sm text-sm transition-colors {{ $viewMode === 'list' ? 'bg-white text-indigo-600 shadow-sm dark:bg-gray-800 dark:text-indigo-300' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200' }}">
                                <i class="fa-solid fa-list"></i>
                            </button>
                        </div>

                        <button type="button" wire:click="clearFilters" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900">
                            Reset filters
                        </button>
                    </div>
                </div>

                @if($viewMode === 'grid')
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach($listings as $listing)
                            @php
                                $product = $listing->variant?->product;
                                $title = data_get($listing, 'display_name') ?: ($product?->name ?? 'Untitled listing');
                                $imageUrl = $product?->image_thumbnail_path ? \App\Utilities\StorageUtility::getUrl($product->image_thumbnail_path) : null;
                                $categories = $product?->display_categories ?? collect();
                            @endphp

                            <a wire:key="listing-{{ $listing->id }}" href="{{ route('app.products.show', ['product' => $product?->slug, 'listing' => $listing->slug]) }}" wire:navigate.hover aria-label="View {{ $title }}" class="group relative block overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:border-gray-800 dark:bg-gray-900 dark:focus-visible:ring-offset-gray-950">
                                <div class="relative aspect-[4/3] overflow-hidden bg-gray-100 dark:bg-gray-800">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $title }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110">
                                    @else
                                        <div class="flex h-full items-center justify-center bg-gradient-to-br from-gray-800 to-gray-700 text-white">
                                            <i class="fa-solid fa-gamepad text-3xl opacity-70"></i>
                                        </div>
                                    @endif

                                    <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
                                        <div class="absolute left-[-45%] top-[-35%] h-[170%] w-[38%] rotate-12 bg-white/35 blur-sm opacity-0 transition-all duration-700 ease-out will-change-transform group-hover:translate-x-[260%] group-hover:translate-y-[260%] group-hover:opacity-100"></div>
                                    </div>

                                    <div class="absolute right-3 top-3 rounded-full bg-white/95 px-3 py-1 text-sm font-semibold text-gray-900 shadow-sm dark:bg-gray-950/95 dark:text-white">
                                        {{ number_format((float) $listing->price, 0, ',', '.') }} VND
                                    </div>

                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-gray-950/80 via-gray-950/30 to-transparent p-3">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($categories->take(2) as $category)
                                                <span class="rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-medium text-white backdrop-blur">{{ data_get($category, 'name', $category->name ?? '') }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3 p-3">
                                    <div>
                                        <h2 class="line-clamp-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
                                        <p class="mt-1 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $product?->name ?? 'Unknown product' }}
                                            @if($listing->variant?->edition)
                                                <span class="text-gray-300 dark:text-gray-600">•</span> {{ $listing->variant->edition }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                                        <div class="rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                            <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Region</div>
                                            <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->region?->name ?? '--' }}</div>
                                        </div>
                                        <div class="rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                            <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Platform</div>
                                            <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->platform?->name ?? '--' }}</div>
                                        </div>
                                        <div class="rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                            <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">OS</div>
                                            <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->operatingSystem?->name ?? '--' }}</div>
                                        </div>
                                        <div class="rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                            <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Stock</div>
                                            <div class="mt-1 font-medium {{ $listing->stock_count > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $listing->stock_count > 0 ? $listing->stock_count.' keys' : 'Sold out' }}</div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-1">
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($listings as $listing)
                            @php
                                $product = $listing->variant?->product;
                                $title = data_get($listing, 'display_name') ?: ($product?->name ?? 'Untitled listing');
                                $imageUrl = $product?->image_thumbnail_path ? \App\Utilities\StorageUtility::getUrl($product->image_thumbnail_path) : null;
                                $categories = $product?->display_categories ?? collect();
                            @endphp

                            <a wire:key="listing-list-{{ $listing->id }}" href="{{ route('app.products.show', ['product' => $product?->slug, 'listing' => $listing->slug]) }}" wire:navigate.hover aria-label="View {{ $title }}" class="group block overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:border-gray-800 dark:bg-gray-900 dark:focus-visible:ring-offset-gray-950">
                                <div class="flex flex-col gap-0 sm:flex-row">
                                    <div class="relative w-full sm:w-44 lg:w-52 shrink-0 aspect-[4/3] sm:aspect-auto sm:min-h-[150px] overflow-hidden bg-gray-100 dark:bg-gray-800">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $title }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110">
                                        @else
                                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-gray-800 to-gray-700 text-white">
                                                <i class="fa-solid fa-gamepad text-3xl opacity-70"></i>
                                            </div>
                                        @endif

                                        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
                                            <div class="absolute left-[-45%] top-[-35%] h-[170%] w-[38%] rotate-12 bg-white/35 blur-sm opacity-0 transition-all duration-700 ease-out will-change-transform group-hover:translate-x-[260%] group-hover:translate-y-[260%] group-hover:opacity-100"></div>
                                        </div>
                                    </div>

                                    <div class="flex flex-1 flex-col gap-3 p-3 sm:p-4">
                                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                            <div class="min-w-0 space-y-2">
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($categories->take(2) as $category)
                                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ data_get($category, 'name', $category->name ?? '') }}</span>
                                                    @endforeach
                                                </div>

                                                <div>
                                                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $product?->name ?? 'Unknown product' }}
                                                        @if($listing->variant?->edition)
                                                            <span class="text-gray-300 dark:text-gray-600">•</span> {{ $listing->variant->edition }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-left dark:border-gray-800 dark:bg-gray-800/70 lg:text-right">
                                                <div class="text-xs uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Price</div>
                                                <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ number_format((float) $listing->price, 0, ',', '.') }} VND</div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 text-[11px] sm:grid-cols-4">
                                            <div class="rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                                <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Region</div>
                                                <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->region?->name ?? '--' }}</div>
                                            </div>
                                            <div class="rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                                <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Platform</div>
                                                <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->platform?->name ?? '--' }}</div>
                                            </div>
                                            <div class="rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                                <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">OS</div>
                                                <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->operatingSystem?->name ?? '--' }}</div>
                                            </div>
                                            <div class="rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                                <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Stock</div>
                                                <div class="mt-1 font-medium {{ $listing->stock_count > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $listing->stock_count > 0 ? $listing->stock_count.' keys' : 'Sold out' }}</div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($listings->isEmpty())
                    <div class="rounded-md border border-dashed border-gray-300 bg-white p-10 text-center dark:border-gray-700 dark:bg-gray-900">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">No listings found</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Try clearing filters or broadening your search.</p>
                        <button type="button" wire:click="clearFilters" class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Clear filters
                        </button>
                    </div>
                @endif

                <div class="pt-1">
                    {{ $listings->links() }}
                </div>
            </section>
        </div>
    </div>
</div>
