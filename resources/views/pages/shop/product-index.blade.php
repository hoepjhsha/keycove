<div class="relative overflow-hidden px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-gradient-to-b from-[#F6EBD9] via-[#FCF9F4] to-transparent dark:from-gray-900 dark:via-gray-950"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -top-8 right-0 -z-10 h-56 w-56 rounded-full bg-[#D32F2F]/8 blur-3xl dark:bg-[#D32F2F]/10"></div>
    <div aria-hidden="true" class="pointer-events-none absolute left-0 top-32 -z-10 h-56 w-56 rounded-full bg-indigo-500/8 blur-3xl dark:bg-indigo-400/10"></div>

    <div class="mx-auto max-w-7xl space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-5 shadow-[0_24px_80px_-40px_rgba(0,0,0,0.35)] sm:p-6 lg:p-8 dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
            <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 hidden w-1/2 bg-radial from-white/80 via-white/10 to-transparent lg:block dark:from-white/10 dark:via-white/5"></div>
            <div class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end">
                <div class="space-y-4">
                    <nav aria-label="Breadcrumb">
                        <ol class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <li>
                                <a href="{{ route('app.products.index') }}" class="transition-colors hover:text-[#D32F2F] dark:hover:text-[#ff8b8b]">
                                    <i class="fa-solid fa-house"></i>
                                </a>
                            </li>
                            <li>
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </li>
                            <li class="font-semibold text-gray-800 dark:text-gray-200" aria-current="page">Products</li>
                        </ol>
                    </nav>

                    <div class="space-y-3">
                        <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] shadow-sm dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#D32F2F]"></span>
                            Shop catalog
                        </p>
                        <div class="space-y-2">
                            <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-gray-950 sm:text-4xl dark:text-white">Browse curated admin listings with a cleaner storefront feel</h1>
                            <p class="max-w-2xl text-sm leading-6 text-gray-600 sm:text-base dark:text-gray-400">Find ready-to-buy game keys faster with richer cards, sharper filters, and a calmer browsing layout that feels closer to a premium digital shelf.</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Live catalog</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($availableListings) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Listings currently searchable</p>
                    </div>

                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Visible now</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $listings->count() }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Listings on this page</p>
                    </div>

                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">View mode</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ ucfirst($viewMode) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Switch between grid and list</p>
                    </div>
                </div>
            </div>
        </section>

        @php
            $selectedCategory = collect($categories)->first(fn ($option) => data_get($option, 'slug', $option->slug ?? '') === $category);
            $selectedPlatform = collect($platforms)->first(fn ($option) => data_get($option, 'slug', $option->slug ?? '') === $platform);
            $selectedRegion = collect($regions)->first(fn ($option) => data_get($option, 'slug', $option->slug ?? '') === $region);
            $selectedOs = collect($operatingSystems)->first(fn ($option) => data_get($option, 'slug', $option->slug ?? '') === $os);

            $hasActiveFilters = filled($search)
                || filled($category)
                || filled($platform)
                || filled($region)
                || filled($os)
                || filled($edition)
                || filled($minPrice)
                || filled($maxPrice)
                || (bool) $inStock;

            $activeFilterTags = collect([
                filled($search) ? 'Search: '.trim($search) : null,
                $selectedCategory ? 'Category: '.data_get($selectedCategory, 'name', $selectedCategory->name ?? '') : null,
                $selectedPlatform ? 'Platform: '.data_get($selectedPlatform, 'name', $selectedPlatform->name ?? '') : null,
                $selectedRegion ? 'Region: '.data_get($selectedRegion, 'name', $selectedRegion->name ?? '') : null,
                $selectedOs ? 'OS: '.data_get($selectedOs, 'name', $selectedOs->name ?? '') : null,
                filled($edition) ? 'Edition: '.trim($edition) : null,
                filled($minPrice) ? 'Min: '.number_format((float) $minPrice, 0, ',', '.').' VND' : null,
                filled($maxPrice) ? 'Max: '.number_format((float) $maxPrice, 0, ',', '.').' VND' : null,
                $inStock ? 'Only in stock' : null,
            ])->filter()->values();

            $activeFilterCount = $activeFilterTags->count();
        @endphp

        <div x-data="{ filtersOpen: @js($hasActiveFilters) }" class="space-y-5">
            <section class="rounded-[2rem] border border-black/8 bg-white/85 p-5 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur-xl dark:border-white/10 dark:bg-gray-900/80">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Refine storefront</p>

                            @if($hasActiveFilters)
                                <span class="rounded-full bg-[#D32F2F]/10 px-2.5 py-1 text-[11px] font-semibold text-[#D32F2F] dark:bg-[#D32F2F]/15 dark:text-[#ff9c9c]">{{ $activeFilterCount }} active</span>
                            @endif
                        </div>

                        <p class="text-sm text-gray-600 dark:text-gray-400">Use the quick panel to zero in on edition, platform, region, and price without overwhelming the product grid.</p>

                        @if($hasActiveFilters)
                            <div class="flex flex-wrap gap-2 pt-1">
                                @foreach($activeFilterTags as $tag)
                                    <span class="rounded-full border border-black/8 bg-[#FCF9F4] px-3 py-1 text-[11px] font-medium text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" x-on:click="filtersOpen = ! filtersOpen" x-bind:aria-expanded="filtersOpen.toString()" aria-controls="shop-filters-panel" class="inline-flex items-center gap-2 rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                            <i class="fa-solid fa-sliders"></i>
                            <span x-text="filtersOpen ? 'Hide filters' : 'Show filters'"></span>
                        </button>

                        <button type="button" wire:click="clearFilters" @disabled(! $hasActiveFilters) class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-[#D32F2F]/25 hover:text-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                            Clear filters
                        </button>
                    </div>
                </div>

                <div id="shop-filters-panel" x-show="filtersOpen" x-transition.opacity.duration.200ms style="display: none;" class="mt-5 border-t border-black/8 pt-5 dark:border-white/10">
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div class="md:col-span-2 xl:col-span-2">
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Title, product, edition..." class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
                            <select wire:model.live="category" class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                                <option value="">All categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ data_get($category, 'slug') }}">{{ data_get($category, 'name') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Sort</label>
                            <select wire:model.live="sortBy" class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                                @foreach($sortOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Platform</label>
                            <select wire:model.live="platform" class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                                <option value="">All</option>
                                @foreach($platforms as $platform)
                                    <option value="{{ $platform->slug }}">{{ $platform->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Region</label>
                            <select wire:model.live="region" class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                                <option value="">All</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->slug }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Operating system</label>
                            <select wire:model.live="os" class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                                <option value="">All operating systems</option>
                                @foreach($operatingSystems as $os)
                                    <option value="{{ $os->slug }}">{{ $os->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Edition</label>
                            <input wire:model.live.debounce.300ms="edition" type="text" placeholder="Standard, Deluxe..." class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Min price</label>
                            <input wire:model.live.debounce.300ms="minPrice" type="number" min="0" step="0.01" placeholder="0" class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Max price</label>
                            <input wire:model.live.debounce.300ms="maxPrice" type="number" min="0" step="0.01" placeholder="0" class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                        </div>

                        <div class="flex items-end">
                            <label class="flex w-full items-center gap-3 rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm dark:border-white/10 dark:bg-gray-950 dark:text-gray-200">
                                <input wire:model.live="inStock" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                Only in stock
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <section class="space-y-5">
                <div class="flex flex-col gap-3 rounded-[1.75rem] border border-black/8 bg-white/85 px-4 py-4 shadow-[0_24px_60px_-40px_rgba(0,0,0,0.45)] backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:border-white/10 dark:bg-gray-900/80">
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Showing <span class="font-semibold text-gray-900 dark:text-white">{{ $listings->count() }}</span> of <span class="font-semibold text-gray-900 dark:text-white">{{ $availableListings }}</span>
                        </p>
                        <p class="text-xs uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Fresh layout, same filtering engine</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if($hasActiveFilters)
                            <span class="rounded-full bg-[#D32F2F]/10 px-3 py-1 text-[11px] font-semibold text-[#D32F2F] dark:bg-[#D32F2F]/15 dark:text-[#ff9c9c]">{{ $activeFilterCount }} filters active</span>
                        @endif

                        <span class="rounded-full border border-black/8 bg-[#FCF9F4] px-3 py-1 text-[11px] font-medium text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">{{ ucfirst($viewMode) }} mode</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <div class="inline-flex rounded-2xl border border-black/8 bg-[#FCF9F4] p-1.5 dark:border-white/10 dark:bg-gray-950">
                            <button type="button" wire:click="setViewMode('grid')" title="Grid view" aria-label="Grid view" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-sm transition-colors {{ $viewMode === 'grid' ? 'bg-white text-[#D32F2F] shadow-sm dark:bg-gray-800 dark:text-[#ff9c9c]' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200' }}">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <button type="button" wire:click="setViewMode('list')" title="List view" aria-label="List view" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-sm transition-colors {{ $viewMode === 'list' ? 'bg-white text-[#D32F2F] shadow-sm dark:bg-gray-800 dark:text-[#ff9c9c]' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200' }}">
                                <i class="fa-solid fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>

                @if($viewMode === 'grid')
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 2xl:grid-cols-4 xl:grid-cols-3">
                        @foreach($listings as $listing)
                            @php
                                $product = $listing->variant?->product;
                                $title = data_get($listing, 'display_name') ?: ($product?->name ?? 'Untitled listing');
                                $imageUrl = $product?->image_thumbnail_path ? \App\Utilities\StorageUtility::getUrl($product->image_thumbnail_path) : null;
                                $categories = $product?->display_categories ?? collect();
                            @endphp

                            <div wire:key="listing-{{ $listing->id }}" class="group relative overflow-hidden rounded-[1.75rem] border border-black/8 bg-white/95 shadow-[0_24px_60px_-38px_rgba(0,0,0,0.45)] transition-all duration-300 hover:-translate-y-1.5 hover:border-[#D32F2F]/15 hover:shadow-[0_32px_80px_-38px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900/95">
                                <a href="{{ route('app.products.show', ['product' => $product?->slug, 'listing' => $listing->slug]) }}" wire:navigate.hover aria-label="View {{ $title }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-[#D32F2F] focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-950">
                                    <div class="relative aspect-[5/4] overflow-hidden bg-gray-100 dark:bg-gray-800">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $title }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110">
                                        @else
                                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-[#D32F2F] text-white">
                                                <i class="fa-solid fa-gamepad text-3xl opacity-70"></i>
                                            </div>
                                        @endif

                                        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
                                            <div class="absolute left-[-45%] top-[-35%] h-[170%] w-[38%] rotate-12 bg-white/35 blur-sm opacity-0 transition-all duration-700 ease-out will-change-transform group-hover:translate-x-[260%] group-hover:translate-y-[260%] group-hover:opacity-100"></div>
                                        </div>

                                        <div class="absolute right-3 top-3 rounded-full bg-white/95 px-3.5 py-1.5 text-sm font-semibold text-gray-900 shadow-lg shadow-black/10 dark:bg-gray-950/95 dark:text-white">
                                            {{ number_format((float) $listing->price, 0, ',', '.') }} VND
                                        </div>

                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-gray-950/90 via-gray-950/45 to-transparent p-4">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($categories->take(3) as $category)
                                                    <span class="rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-medium text-white backdrop-blur">{{ data_get($category, 'name', $category->name ?? '') }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-4 p-4">
                                        <div>
                                            <h2 class="line-clamp-1 text-base font-semibold text-gray-950 dark:text-white">{{ $title }}</h2>
                                            <p class="mt-1 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $product?->name ?? 'Unknown product' }}
                                                @if($listing->variant?->edition)
                                                    <span class="text-gray-300 dark:text-gray-600">•</span> {{ $listing->variant->edition }}
                                                @endif
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                                            <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                                <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Region</div>
                                                <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->region?->name ?? '--' }}</div>
                                            </div>
                                            <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                                <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Platform</div>
                                                <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->platform?->name ?? '--' }}</div>
                                            </div>
                                            <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                                <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">OS</div>
                                                <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->operatingSystem?->name ?? '--' }}</div>
                                            </div>
                                            <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                                <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Stock</div>
                                                <div class="mt-1 font-medium {{ $listing->stock_count > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $listing->stock_count > 0 ? $listing->stock_count.' keys' : 'Sold out' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                                <div x-data="{ wishlistPulse: false, cartPulse: false, pulse(key) { this[key] = false; requestAnimationFrame(() => { this[key] = true; window.setTimeout(() => this[key] = false, 550); }); } }" class="flex flex-wrap items-center gap-2 border-t border-black/6 px-4 pb-4 pt-1 dark:border-white/8">
                                    <button
                                        type="button"
                                        x-on:click.stop.prevent="pulse('wishlistPulse'); $dispatch('shop:wishlist:add', {
                                            id: {{ $listing->id }},
                                            title: @js($title),
                                            subtitle: @js(collect([$product?->name, $listing->variant?->edition])->filter()->implode(' • ')),
                                            price: @js((float) $listing->price),
                                            url: @js(route('app.products.show', ['product' => $product?->slug, 'listing' => $listing->slug])),
                                            image: @js($imageUrl),
                                        })"
                                        x-bind:class="wishlistPulse ? 'scale-[1.03] border-[#D32F2F]/35 text-[#D32F2F] shadow-lg shadow-[#D32F2F]/10 dark:text-[#ffb1b1]' : ''"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-black/10 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                                        <i class="fa-regular fa-heart text-[12px] transition-transform duration-300" x-bind:class="wishlistPulse ? 'scale-125' : ''"></i>
                                        Wishlist
                                    </button>

                                    <button
                                        type="button"
                                        x-on:click="pulse('cartPulse')"
                                        wire:click.stop.prevent="addToCart({{ $listing->id }})"
                                        x-bind:class="cartPulse ? 'scale-[1.03] bg-[#D32F2F] shadow-lg shadow-[#D32F2F]/20 dark:bg-[#D32F2F] dark:text-white' : ''"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-black px-3 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#D32F2F] data-loading:pointer-events-none data-loading:scale-[0.98] data-loading:opacity-90 dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                        <i class="fa-solid fa-cart-shopping text-[12px] transition-transform duration-300" x-bind:class="cartPulse ? 'scale-125' : ''"></i>
                                        Add to cart
                                    </button>
                                </div>
                            </div>
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

                            <div wire:key="listing-list-{{ $listing->id }}" class="group overflow-hidden rounded-[1.75rem] border border-black/8 bg-white/95 shadow-[0_24px_60px_-38px_rgba(0,0,0,0.45)] transition-all duration-300 hover:-translate-y-1 hover:border-[#D32F2F]/15 hover:shadow-[0_32px_80px_-38px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900/95">
                                <a href="{{ route('app.products.show', ['product' => $product?->slug, 'listing' => $listing->slug]) }}" wire:navigate.hover aria-label="View {{ $title }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-[#D32F2F] focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-950">
                                    <div class="flex flex-col gap-0 sm:flex-row">
                                        <div class="relative aspect-[4/3] w-full shrink-0 overflow-hidden bg-gray-100 sm:w-44 sm:aspect-auto sm:min-h-[170px] lg:w-56 dark:bg-gray-800">
                                            @if($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="{{ $title }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110">
                                            @else
                                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-[#D32F2F] text-white">
                                                    <i class="fa-solid fa-gamepad text-3xl opacity-70"></i>
                                                </div>
                                            @endif

                                            <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
                                                <div class="absolute left-[-45%] top-[-35%] h-[170%] w-[38%] rotate-12 bg-white/35 blur-sm opacity-0 transition-all duration-700 ease-out will-change-transform group-hover:translate-x-[260%] group-hover:translate-y-[260%] group-hover:opacity-100"></div>
                                            </div>
                                        </div>

                                        <div class="flex flex-1 flex-col gap-4 p-4 sm:p-5">
                                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                                <div class="min-w-0 space-y-2">
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($categories->take(2) as $category)
                                                            <span class="rounded-full bg-[#FCF9F4] px-2.5 py-1 text-[10px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ data_get($category, 'name', $category->name ?? '') }}</span>
                                                        @endforeach
                                                    </div>

                                                    <div>
                                                        <h2 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $title }}</h2>
                                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $product?->name ?? 'Unknown product' }}
                                                            @if($listing->variant?->edition)
                                                                <span class="text-gray-300 dark:text-gray-600">•</span> {{ $listing->variant->edition }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-3 text-left shadow-sm dark:border-white/10 dark:bg-gray-800/70 lg:text-right">
                                                    <div class="text-xs uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Price</div>
                                                    <div class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ number_format((float) $listing->price, 0, ',', '.') }} VND</div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2 text-[11px] sm:grid-cols-4">
                                                <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                                    <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Region</div>
                                                    <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->region?->name ?? '--' }}</div>
                                                </div>
                                                <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                                    <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Platform</div>
                                                    <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->platform?->name ?? '--' }}</div>
                                                </div>
                                                <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                                    <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">OS</div>
                                                    <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing->variant?->operatingSystem?->name ?? '--' }}</div>
                                                </div>
                                                <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                                    <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Stock</div>
                                                    <div class="mt-1 font-medium {{ $listing->stock_count > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $listing->stock_count > 0 ? $listing->stock_count.' keys' : 'Sold out' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                                <div x-data="{ wishlistPulse: false, cartPulse: false, pulse(key) { this[key] = false; requestAnimationFrame(() => { this[key] = true; window.setTimeout(() => this[key] = false, 550); }); } }" class="flex flex-wrap items-center gap-2 border-t border-black/6 px-4 pb-4 pt-1 text-sm dark:border-white/8 sm:px-5">
                                    <button
                                        type="button"
                                        x-on:click.stop.prevent="pulse('wishlistPulse'); $dispatch('shop:wishlist:add', {
                                            id: {{ $listing->id }},
                                            title: @js($title),
                                            subtitle: @js(collect([$product?->name, $listing->variant?->edition])->filter()->implode(' • ')),
                                            price: @js((float) $listing->price),
                                            url: @js(route('app.products.show', ['product' => $product?->slug, 'listing' => $listing->slug])),
                                            image: @js($imageUrl),
                                        })"
                                        x-bind:class="wishlistPulse ? 'scale-[1.03] border-[#D32F2F]/35 text-[#D32F2F] shadow-lg shadow-[#D32F2F]/10 dark:text-[#ffb1b1]' : ''"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-black/10 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                                        <i class="fa-regular fa-heart text-[12px] transition-transform duration-300" x-bind:class="wishlistPulse ? 'scale-125' : ''"></i>
                                        Wishlist
                                    </button>

                                    <button
                                        type="button"
                                        x-on:click="pulse('cartPulse')"
                                        wire:click.stop.prevent="addToCart({{ $listing->id }})"
                                        x-bind:class="cartPulse ? 'scale-[1.03] bg-[#D32F2F] shadow-lg shadow-[#D32F2F]/20 dark:bg-[#D32F2F] dark:text-white' : ''"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-black px-3 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#D32F2F] data-loading:pointer-events-none data-loading:scale-[0.98] data-loading:opacity-90 dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                        <i class="fa-solid fa-cart-shopping text-[12px] transition-transform duration-300" x-bind:class="cartPulse ? 'scale-125' : ''"></i>
                                        Add to cart
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($listings->isEmpty())
                    <div class="rounded-[2rem] border border-dashed border-black/15 bg-white/90 p-10 text-center shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#FCF9F4] text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-gray-950 dark:text-white">No listings found</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Try clearing filters, widening the price range, or browsing a broader platform mix.</p>
                        <button type="button" wire:click="clearFilters" class="mt-5 inline-flex items-center rounded-2xl bg-black px-5 py-3 text-sm font-medium text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                            Clear filters
                        </button>
                    </div>
                @endif

                <div class="rounded-[1.75rem] border border-black/8 bg-white/85 px-4 py-3 shadow-[0_24px_60px_-40px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/80">
                    {{ $listings->links() }}
                </div>
            </section>
        </div>
    </div>
</div>
