<div class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <li>
                    <a href="{{ route('app.products.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <i class="fa-solid fa-house"></i>
                    </a>
                </li>
                <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
                <li>
                    <a href="{{ route('app.products.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Products</a>
                </li>
                <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
                <li class="font-semibold text-gray-800 dark:text-gray-200" aria-current="page">{{ $displayTitle }}</li>
            </ol>
        </nav>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <section class="space-y-6">
                <div class="rounded-md border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="grid gap-0 lg:grid-cols-[18rem_minmax(0,1fr)]">
                        <div class="relative aspect-square bg-gray-100 dark:bg-gray-800 lg:aspect-auto lg:min-h-[420px]">
                            @if($productImage)
                                <img src="{{ $productImage }}" alt="{{ $displayTitle }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-gray-800 to-gray-700 text-white">
                                    <i class="fa-solid fa-gamepad text-4xl opacity-70"></i>
                                </div>
                            @endif
                        </div>

                        <div class="p-5 sm:p-6 space-y-5">
                            <div class="space-y-2">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-gray-900 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-white">Shop</span>
                                    @if($listing->stock_count > 0)
                                        <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">In stock</span>
                                    @else
                                        <span class="rounded-full bg-rose-500/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-rose-700 dark:text-rose-300">Sold out</span>
                                    @endif
                                </div>

                                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white sm:text-3xl">{{ $displayTitle }}</h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->name }} • {{ $listing->variant?->edition ?? 'Standard' }}</p>
                            </div>

                            <div class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-950">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Price</p>
                                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format((float) $listing->price, 0, ',', '.') }} VND</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Available keys</p>
                                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format((int) ($listing->available_keys_count ?? 0)) }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
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

                            @if(! empty($product->system_requirement))
                                <div class="space-y-3">
                                    <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">System requirements</h2>
                                    <dl class="grid gap-3 sm:grid-cols-2">
                                        @foreach($product->system_requirement as $key => $value)
                                            <div class="rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                                <dt class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">{{ $key }}</dt>
                                                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $value }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-md border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="space-y-3">
                        <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Description</h2>
                        <p class="text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $product->description ?? 'No description available.' }}</p>
                    </div>
                </div>
            </section>

            <aside class="space-y-5 lg:sticky lg:top-6 lg:self-start">
                <div class="rounded-md border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Meta</p>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-gray-500 dark:text-gray-400">Publisher</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $product->publisher ?? '--' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-gray-500 dark:text-gray-400">Developer</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $product->developer ?? '--' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-gray-500 dark:text-gray-400">Release date</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $product->release_date?->format('d/m/Y') ?? '--' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-gray-500 dark:text-gray-400">Categories</span>
                            <span class="font-medium text-gray-900 dark:text-white text-right">{{ $product->display_categories->pluck('name')->join(', ') }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-md border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Actions</p>
                    <div class="mt-4 space-y-3">
                        <button type="button" class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Add to cart</button>
                        <button type="button" class="w-full rounded-md border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900">Wishlist</button>
                    </div>
                </div>
            </aside>
        </div>

        @if($relatedListings->isNotEmpty())
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Related listings</h2>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($relatedListings as $related)
                        @php
                            $relatedProduct = $related->variant?->product;
                            $relatedTitle = data_get($related, 'display_name') ?: ($relatedProduct?->name ?? 'Untitled listing');
                            $relatedImage = $relatedProduct?->image_thumbnail_path ? \App\Utilities\StorageUtility::getUrl($relatedProduct->image_thumbnail_path) : null;
                        @endphp

                            <a href="{{ route('app.products.show', ['product' => $relatedProduct?->slug, 'listing' => $related->slug]) }}" wire:navigate.hover class="group overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                            <div class="relative aspect-[4/3] bg-gray-100 dark:bg-gray-800">
                                @if($relatedImage)
                                    <img src="{{ $relatedImage }}" alt="{{ $relatedTitle }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="p-3">
                                <h3 class="line-clamp-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $relatedTitle }}</h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ number_format((float) $related->price, 0, ',', '.') }} VND</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
