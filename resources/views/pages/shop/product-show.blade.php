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

                <div class="rounded-md border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Reviews</h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">What buyers said after completing their order.</p>
                        </div>

                        <div class="text-right">
                            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($productReviewAverage, 1) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $productReviewCount }} review{{ $productReviewCount === 1 ? '' : 's' }}</div>
                        </div>
                    </div>

                    @if($productReviewCount > 0)
                        <div class="mt-5 space-y-4">
                            @foreach($productReviews as $review)
                                <article class="rounded-md border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ $review['user_name'] }}</p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $review['created_at'] ?? '--' }}</p>
                                        </div>

                                        <div class="flex items-center gap-1 text-amber-400">
                                            @for($star = 1; $star <= 5; $star++)
                                                <i class="fa-solid fa-star {{ $star <= $review['rating'] ? '' : 'text-gray-300 dark:text-gray-600' }}"></i>
                                            @endfor
                                        </div>
                                    </div>

                                    @if(filled($review['comment']))
                                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $review['comment'] }}</p>
                                    @endif

                                    @if(($review['media'] ?? []) !== [])
                                        <div x-data="{ previewUrl: null }" class="mt-4 space-y-2 border-t border-gray-200 pt-4 dark:border-gray-800">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Media</p>
                                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                @foreach($review['media'] as $media)
                                                    @php
                                                        $extension = strtolower(pathinfo($media['label'], PATHINFO_EXTENSION));
                                                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true);
                                                        $isPdf = $extension === 'pdf';
                                                    @endphp

                                                    @if($media['url'] && $isImage)
                                                        <button type="button" x-on:click="previewUrl = @js($media['url'])" class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-indigo-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-950 dark:hover:border-indigo-500/30">
                                                            <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-900">
                                                                <img src="{{ $media['url'] }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                                                            </div>
                                                        </button>
                                                    @elseif($media['url'] && $isPdf)
                                                        <a href="{{ $media['url'] }}" target="_blank" rel="noopener noreferrer" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-indigo-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-950 dark:hover:border-indigo-500/30">
                                                            <div class="flex items-center justify-center border-b border-gray-200 bg-gray-50 px-3 py-3 dark:border-gray-800 dark:bg-gray-900">
                                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 text-red-600 dark:text-red-300">
                                                                    <i class="fa-solid fa-file-pdf"></i>
                                                                </div>
                                                            </div>
                                                            <object data="{{ $media['url'] }}" type="application/pdf" class="h-64 w-full">
                                                                <div class="p-3 text-sm text-gray-500 dark:text-gray-400">PDF preview unavailable.</div>
                                                            </object>
                                                        </a>
                                                    @elseif($media['url'])
                                                        <a href="{{ $media['url'] }}" target="_blank" rel="noopener noreferrer" class="block rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:border-indigo-500/30 dark:hover:text-indigo-300">
                                                            {{ $media['label'] }}
                                                        </a>
                                                    @else
                                                        <div class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                                                            {{ $media['label'] }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>

                                            <div
                                                x-cloak
                                                x-show="previewUrl"
                                                x-transition.opacity
                                                x-on:click.self="previewUrl = null"
                                                class="fixed inset-0 z-[90] flex items-center justify-center bg-black/75 p-4"
                                            >
                                                <button type="button" x-on:click="previewUrl = null" class="absolute inset-0 cursor-default" aria-label="Close preview"></button>
                                                <div class="relative z-10 max-h-[90vh] max-w-[92vw] overflow-hidden rounded-2xl bg-black shadow-2xl">
                                                    <img :src="previewUrl" alt="" class="max-h-[90vh] max-w-[92vw] object-contain">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-5 rounded-md border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
                            No reviews yet.
                        </div>
                    @endif
                </div>
            </section>

            <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
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
                    <div x-data="{ wishlistPulse: false, cartPulse: false, pulse(key) { this[key] = false; requestAnimationFrame(() => { this[key] = true; window.setTimeout(() => this[key] = false, 550); }); } }" class="mt-4 space-y-3">
                        <button type="button" x-on:click="pulse('cartPulse')" wire:click.stop.prevent="addToCart({{ $listing->id }})" x-bind:class="cartPulse ? 'scale-[1.02] bg-[#D32F2F] shadow-lg shadow-[#D32F2F]/20' : ''" class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition-all duration-300 hover:bg-indigo-700 data-loading:pointer-events-none data-loading:scale-[0.98] data-loading:opacity-90">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-solid fa-cart-shopping text-[12px] transition-transform duration-300" x-bind:class="cartPulse ? 'scale-125' : ''"></i>
                                Add to cart
                            </span>
                        </button>
                        <button
                            type="button"
                            x-on:click.stop.prevent="pulse('wishlistPulse'); $dispatch('shop:wishlist:add', {
                                id: {{ $listing->id }},
                                title: @js($displayTitle),
                                subtitle: @js(collect([$product->name, $listing->variant?->edition])->filter()->implode(' • ')),
                                price: @js((float) $listing->price),
                                url: @js(route('app.products.show', ['product' => $product->slug, 'listing' => $listing->slug])),
                                image: @js($productImage),
                            })"
                            x-bind:class="wishlistPulse ? 'scale-[1.02] border-[#D32F2F]/35 text-[#D32F2F] shadow-lg shadow-[#D32F2F]/10 dark:text-[#ffb1b1]' : ''"
                            class="w-full rounded-md border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-regular fa-heart text-[12px] transition-transform duration-300" x-bind:class="wishlistPulse ? 'scale-125' : ''"></i>
                                Wishlist
                            </span>
                        </button>
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
