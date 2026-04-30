<div class="space-y-6">
    @php
        $libraryUrl = \Illuminate\Support\Facades\Route::has('app.library.show')
            ? route('app.library.show')
            : url('/my-library');
    @endphp

    @if(session('seller-status'))
        <div class="rounded-[1.75rem] border border-emerald-500/15 bg-emerald-500/8 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
            {{ session('seller-status') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3">
                <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-[#FCF9F4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                    Seller dashboard
                </p>
                <div>
                    <h2 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">Welcome back, {{ $seller->shop_name }}</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">Track your shop health, wallet balances, and recent inventory from one place.</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('seller.apply') }}" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                        Open application
                    </a>
                    <a href="{{ route('seller.listings.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                        Manage listings
                    </a>
                    <a href="{{ $libraryUrl }}" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                        My Library
                    </a>
                </div>
            </div>

            <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">KYC status</p>
                <div class="mt-3 inline-flex rounded-full px-3 py-1 text-[11px] font-semibold {{ $portalStatus['badgeClass'] }}">{{ $portalStatus['badge'] }}</div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Seller since {{ $seller->created_at?->format('d/m/Y') ?? '--' }}</p>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Products</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['products']) }}</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ number_format($metrics['activeProducts']) }} active</p>
        </article>

        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Listings</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['listings']) }}</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ number_format($metrics['activeListings']) }} active</p>
        </article>

        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Wallet balance</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['walletBalance'], 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Holding {{ number_format($metrics['walletHolding'], 0, ',', '.') }} VND</p>
        </article>

        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Keys available</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['availableKeys']) }}</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Ready for checkout</p>
        </article>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Recent products</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your latest submitted products and their catalog state.</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($recentProducts as $product)
                    <article class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-3 dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h4 class="font-semibold text-gray-950 dark:text-white">{{ $product->name }}</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $product->variants_count }} variants · {{ $product->listings_count }} listings</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $product->status->value === \App\Enums\GeneralStatus::Active->value ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-slate-500/10 text-slate-700 dark:text-slate-300' }}">{{ $product->status->label() }}</span>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-black/15 px-4 py-6 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                        No products yet.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Recent listings</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track inventory that is currently tied to your shop.</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($recentListings as $listing)
                    @php
                        $listingStatusClass = match ($listing->status) {
                            \App\Enums\ProductListingStatus::Active => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                            \App\Enums\ProductListingStatus::Pending => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                            \App\Enums\ProductListingStatus::Rejected => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                            default => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
                        };
                    @endphp

                    <article class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-3 dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h4 class="font-semibold text-gray-950 dark:text-white">{{ $listing->display_name ?: $listing->variant?->product?->name }}</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $listing->variant?->product?->name ?? 'Product' }} · {{ $listing->variant?->edition ?? 'Standard' }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $listingStatusClass }}">{{ $listing->status->label() }}</span>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">
                            <span class="rounded-full border border-black/8 bg-white px-3 py-1 dark:border-white/10 dark:bg-gray-950">{{ number_format((float) $listing->price, 0, ',', '.') }} VND</span>
                            <span class="rounded-full border border-black/8 bg-white px-3 py-1 dark:border-white/10 dark:bg-gray-950">{{ $listing->available_keys_count }} keys</span>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-black/15 px-4 py-6 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                        No listings yet.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
