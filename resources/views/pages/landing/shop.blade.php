<div class="relative overflow-hidden px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-80 bg-gradient-to-b from-[#F6EBD9] via-[#FCF9F4] to-transparent dark:from-gray-900 dark:via-gray-950"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -top-8 right-0 -z-10 h-56 w-56 rounded-full bg-[#D32F2F]/8 blur-3xl dark:bg-[#D32F2F]/12"></div>
    <div aria-hidden="true" class="pointer-events-none absolute left-0 top-36 -z-10 h-56 w-56 rounded-full bg-indigo-500/8 blur-3xl dark:bg-indigo-400/10"></div>

    <div class="mx-auto max-w-7xl space-y-8">
        <section class="relative overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-6 shadow-[0_24px_80px_-40px_rgba(0,0,0,0.35)] sm:p-8 lg:p-10 dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
            <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 hidden w-1/2 bg-radial from-white/80 via-white/10 to-transparent lg:block dark:from-white/10 dark:via-white/5"></div>
            <div class="relative grid gap-8 lg:grid-cols-[minmax(0,1.25fr)_18rem] lg:items-end">
                <div class="space-y-5">
                    <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] shadow-sm dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D32F2F]"></span>
                        Trang chủ marketplace
                    </p>

                    <div class="space-y-3">
                        <h1 class="max-w-4xl text-3xl font-semibold tracking-tight text-gray-950 sm:text-5xl dark:text-white">
                            Tìm nhanh, duyệt theo danh mục và khám phá các listing tốt nhất tại một nơi.
                        </h1>
                        <p class="max-w-2xl text-sm leading-6 text-gray-600 sm:text-base dark:text-gray-400">
                            Bắt đầu từ trang chủ để đi thẳng vào catalog sản phẩm với các bộ lọc quen thuộc.
                        </p>
                    </div>

                    <form action="{{ route('app.products.index') }}" method="GET" class="flex flex-col gap-3 rounded-[1.6rem] border border-black/8 bg-white/90 p-3 shadow-[0_24px_60px_-42px_rgba(0,0,0,0.45)] backdrop-blur sm:flex-row dark:border-white/10 dark:bg-gray-950/80">
                        <label class="flex-1">
                            <span class="sr-only">Tìm sản phẩm</span>
                            <input name="search" value="{{ request('search') }}" type="search" placeholder="Tìm theo tên, nhà phát hành, phiên bản..." class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                        </label>

                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-black px-5 py-3 text-sm font-medium text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                            Tìm kiếm
                        </button>
                    </form>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Danh mục</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($categories->count()) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Truy cập nhanh vào catalog</p>
                    </div>

                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Sản phẩm nổi bật</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($featuredProducts->count()) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sản phẩm bán chạy và gợi ý khám phá</p>
                    </div>

                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Listing người bán</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($sellerListings->count()) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Listing đang hoạt động từ người bán</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Danh mục</p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Duyệt theo danh mục</h2>
                </div>
                <button type="button" wire:click="toggleCategories" class="text-sm font-medium text-[#D32F2F] hover:underline dark:text-[#ff9c9c]">
                    {{ $showAllCategories ? 'Thu gọn' : 'Xem tất cả' }}
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach(($showAllCategories ? $categories : $categories->take(6)) as $category)
                    <a href="{{ route('app.products.index', ['category' => $category['slug']]) }}" wire:navigate.hover class="group rounded-[1.5rem] border border-black/8 bg-white/90 p-4 shadow-[0_20px_40px_-28px_rgba(0,0,0,0.35)] transition-all hover:-translate-y-1 hover:border-[#D32F2F]/20 hover:shadow-[0_24px_50px_-28px_rgba(0,0,0,0.4)] dark:border-white/10 dark:bg-gray-900/90">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#FCF9F4] text-[#D32F2F] transition-colors group-hover:bg-[#D32F2F] group-hover:text-white dark:bg-white/5 dark:text-[#ff9c9c] dark:group-hover:bg-[#D32F2F] dark:group-hover:text-white">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="mt-4 space-y-1">
                            <h3 class="line-clamp-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $category['name'] }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($category['products_count']) }} sản phẩm</p>
                        </div>

                        @if($category['children']->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($category['children']->take(2) as $child)
                                    <span class="rounded-full bg-[#FCF9F4] px-2.5 py-1 text-[10px] font-medium text-gray-700 dark:bg-white/5 dark:text-gray-300">{{ $child['name'] }}</span>
                                @endforeach
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Nổi bật</p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Sản phẩm nổi bật</h2>
                </div>
                <a href="{{ route('app.products.index') }}" wire:navigate.hover class="text-sm font-medium text-[#D32F2F] hover:underline dark:text-[#ff9c9c]">Xem catalog</a>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($featuredProducts as $product)
                    <a href="{{ $product['url'] }}" wire:navigate.hover class="group overflow-hidden rounded-[1.75rem] border border-black/8 bg-white/95 shadow-[0_24px_60px_-38px_rgba(0,0,0,0.45)] transition-all duration-300 hover:-translate-y-1.5 hover:border-[#D32F2F]/15 hover:shadow-[0_32px_80px_-38px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900/95">
                        <div class="relative aspect-[5/4] overflow-hidden bg-gray-100 dark:bg-gray-800">
                            @if($product['image'])
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110">
                            @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-[#D32F2F] text-white">
                                    <i class="fa-solid fa-gamepad text-3xl opacity-70"></i>
                                </div>
                            @endif

                            <div class="absolute right-3 top-3 rounded-full bg-white/95 px-3.5 py-1.5 text-sm font-semibold text-gray-900 shadow-lg shadow-black/10 dark:bg-gray-950/95 dark:text-white">
                                {{ $product['listing'] ? number_format($product['listing']['price'], 0, ',', '.') . ' VND' : 'Mới' }}
                            </div>

                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-gray-950/90 via-gray-950/45 to-transparent p-4">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($product['categories'] as $category)
                                        <span class="rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-medium text-white backdrop-blur">{{ $category['name'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="line-clamp-1 text-base font-semibold text-gray-950 dark:text-white">{{ $product['name'] }}</h3>
                            <p class="mt-1 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $product['publisher'] ?? 'Chưa rõ nhà phát hành' }}
                                @if($product['developer'])
                                    <span class="text-gray-300 dark:text-gray-600">•</span> {{ $product['developer'] }}
                                @endif
                            </p>

                            <div class="mt-4 rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Phạm vi listing</div>
                                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $product['listing'] ? $product['listing']['title'] : 'Xem tất cả listing' }}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Người bán</p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Listing từ người bán</h2>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Listing đang hoạt động mới nhất từ người bán</span>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach($sellerListings as $listing)
                    <article class="overflow-hidden rounded-[1.75rem] border border-black/8 bg-white/95 shadow-[0_24px_60px_-38px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/95">
                        <div class="relative aspect-[5/4] overflow-hidden bg-gray-100 dark:bg-gray-800">
                            @if($listing['image'])
                                <img src="{{ $listing['image'] }}" alt="{{ $listing['title'] }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-[#D32F2F] text-white">
                                    <i class="fa-solid fa-store text-3xl opacity-70"></i>
                                </div>
                            @endif

                            <div class="absolute right-3 top-3 rounded-full bg-white/95 px-3.5 py-1.5 text-sm font-semibold text-gray-900 shadow-lg shadow-black/10 dark:bg-gray-950/95 dark:text-white">
                                {{ number_format($listing['price'], 0, ',', '.') }} VND
                            </div>
                        </div>

                        <div class="space-y-3 p-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#D32F2F] dark:text-[#ff9c9c]">{{ $listing['seller_name'] }}</p>
                                <h3 class="mt-1 line-clamp-2 text-base font-semibold text-gray-950 dark:text-white">{{ $listing['title'] }}</h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $listing['product_name'] }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-[11px]">
                                <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                    <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Tồn kho</div>
                                    <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $listing['stock_count'] > 0 ? $listing['stock_count'].' key' : 'Hết hàng' }}</div>
                                </div>
                                <div class="rounded-2xl bg-[#FCF9F4] px-3 py-2.5 dark:bg-gray-800/80">
                                    <div class="text-[10px] uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Loại</div>
                                    <div class="mt-1 font-medium text-gray-900 dark:text-white">Listing người bán</div>
                                </div>
                            </div>

                            @if($listing['categories']->isNotEmpty())
                                <div class="flex flex-wrap gap-2">
                                    @foreach($listing['categories'] as $category)
                                        <span class="rounded-full bg-[#FCF9F4] px-2.5 py-1 text-[10px] font-medium text-gray-700 dark:bg-white/5 dark:text-gray-300">{{ $category['name'] }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</div>
