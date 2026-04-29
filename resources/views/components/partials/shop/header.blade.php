@php
    $authenticatedUser = auth()->user();
    $isAuthenticated = $authenticatedUser !== null;
    $loginUrl = \Illuminate\Support\Facades\Route::has('app.auth.login')
        ? route('app.auth.login')
        : route('app.shop.index');

    $logoutUrl = \Illuminate\Support\Facades\Route::has('app.auth.logout')
        ? route('app.auth.logout')
        : route('app.shop.index');

    $profileUrl = $isAuthenticated
        ? (
            \Illuminate\Support\Facades\Route::has('app.profile.show')
                ? route('app.profile.show')
                : route('app.shop.index')
        )
        : $loginUrl;

    $cartItemUpdateUrl = route('app.cart.items.update', ['cartItem' => '__CART_ITEM__']);
    $cartItemDestroyUrl = route('app.cart.items.destroy', ['cartItem' => '__CART_ITEM__']);

    $cartItems = collect();

    if ($authenticatedUser !== null) {
        $cartItems = $authenticatedUser->cart()
            ->with(['items.listing.variant.product'])
            ->first()?->items
            ->map(function ($item) {
                $listing = $item->listing;
                $product = $listing?->variant?->product;
                $imageUrl = $product?->image_thumbnail_path
                    ? \App\Utilities\StorageUtility::getUrl($product->image_thumbnail_path)
                    : null;

                return [
                    'id' => $item->id,
                    'listing_id' => $listing?->id,
                    'title' => $listing?->display_name ?: ($product?->name ?? 'Unknown item'),
                    'subtitle' => collect([$product?->name, $listing?->variant?->edition])->filter()->implode(' • '),
                    'quantity' => (int) $item->quantity,
                    'price' => (float) ($listing?->price ?? 0),
                    'stock' => (int) ($listing?->stock_count ?? 0),
                    'url' => $product !== null && $listing !== null
                        ? route('app.products.show', ['product' => $product->slug, 'listing' => $listing->slug])
                        : '#',
                    'image' => $imageUrl,
                ];
            })
            ->values() ?? collect();
    }
@endphp

<header
    x-data="{
        mobileMenuOpen: false,
        searchOpen: false,
        drawerOpen: false,
        activeDrawerTab: 'cart',
        accountMenuOpen: false,
        wishlistCookieName: 'shop_wishlist',
        isAuthenticated: @js($isAuthenticated),
        authName: @js($authenticatedUser?->username ?? $authenticatedUser?->email),
        profileUrl: @js($profileUrl),
        loginUrl: @js($loginUrl),
        logoutUrl: @js($logoutUrl),
        csrfToken: @js(csrf_token()),
        cartItemUpdateUrlTemplate: @js($cartItemUpdateUrl),
        cartItemDestroyUrlTemplate: @js($cartItemDestroyUrl),
        cartItems: @js($cartItems->all()),
        wishlistItems: [],
        pendingCartItemIds: [],
        wishlistPulse: false,
        cartPulse: false,
        actionToastOpen: false,
        actionToast: { type: 'wishlist', label: '', title: '' },
        actionToastTimeout: null,
        pulseTimeouts: { wishlist: null, cart: null },
        init() {
            this.loadWishlist();

            this.$watch('drawerOpen', value => {
                document.body.classList.toggle('overflow-hidden', value);
            });

            window.addEventListener('shop:panel:open', event => {
                const detail = event.detail || {};
                this.openPanel(detail.tab || 'cart');
            });

            window.addEventListener('shop:wishlist:add', event => {
                this.addWishlistItem(event.detail || {});
            });

            window.addEventListener('shop:wishlist:remove', event => {
                const detail = event.detail || {};
                if (detail.id) {
                    this.removeWishlistItem(detail.id);
                }
            });

            window.addEventListener('shop:cart:add', event => {
                this.addCartItem(event.detail?.item || {});
            });
        },
        openPanel(tab = 'cart') {
            this.activeDrawerTab = tab;
            this.accountMenuOpen = false;
            this.drawerOpen = true;
        },
        closePanel() {
            this.drawerOpen = false;
        },
        cartItemUrl(template, itemId) {
            return template.replace('__CART_ITEM__', String(itemId));
        },
        isCartItemPending(itemId) {
            return this.pendingCartItemIds.includes(String(itemId));
        },
        setCartItemPending(itemId, pending) {
            const normalizedId = String(itemId);

            if (pending) {
                if (! this.pendingCartItemIds.includes(normalizedId)) {
                    this.pendingCartItemIds.push(normalizedId);
                }

                return;
            }

            this.pendingCartItemIds = this.pendingCartItemIds.filter(id => id !== normalizedId);
        },
        readCookie(name) {
            const prefix = `${name}=`;
            const cookie = document.cookie
                .split('; ')
                .find(row => row.startsWith(prefix));

            return cookie ? decodeURIComponent(cookie.slice(prefix.length)) : null;
        },
        persistWishlist() {
            const expiresAt = new Date();
            expiresAt.setFullYear(expiresAt.getFullYear() + 1);

            document.cookie = `${this.wishlistCookieName}=${encodeURIComponent(JSON.stringify(this.wishlistItems))}; expires=${expiresAt.toUTCString()}; path=/; SameSite=Lax`;
            window.dispatchEvent(new CustomEvent('shop:wishlist:changed', {
                detail: { count: this.wishlistItems.length, items: this.wishlistItems },
            }));
        },
        loadWishlist() {
            const raw = this.readCookie(this.wishlistCookieName);

            if (! raw) {
                this.wishlistItems = [];
                return;
            }

            try {
                const parsed = JSON.parse(raw);
                this.wishlistItems = Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                this.wishlistItems = [];
            }
        },
        pulseAction(type) {
            const key = type === 'wishlist' ? 'wishlistPulse' : 'cartPulse';

            window.clearTimeout(this.pulseTimeouts[type]);
            this[key] = false;

            requestAnimationFrame(() => {
                this[key] = true;
                this.pulseTimeouts[type] = window.setTimeout(() => {
                    this[key] = false;
                }, 650);
            });
        },
        showActionToast(type, label, title) {
            window.clearTimeout(this.actionToastTimeout);

            this.actionToast = {
                type,
                label,
                title: title || (type === 'wishlist' ? 'Saved item' : 'Cart item'),
            };

            this.actionToastOpen = true;
            this.actionToastTimeout = window.setTimeout(() => {
                this.actionToastOpen = false;
            }, 1800);
        },
        async updateCartItemQuantity(itemId, quantity) {
            if (! this.isAuthenticated || this.isCartItemPending(itemId)) {
                return;
            }

            const existingItem = this.cartItems.find(entry => String(entry.id) === String(itemId));

            if (! existingItem) {
                return;
            }

            this.setCartItemPending(itemId, true);

            try {
                const response = await fetch(this.cartItemUrl(this.cartItemUpdateUrlTemplate, itemId), {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({ quantity }),
                });

                const payload = await response.json();

                if (! response.ok) {
                    throw new Error(payload.message || 'Unable to update cart item.');
                }

                this.cartItems = this.cartItems.map(entry => String(entry.id) === String(itemId) ? payload.item : entry);
                this.pulseAction('cart');
                this.showActionToast('cart', 'Cart updated', payload.item.title);
            } catch (error) {
                this.showActionToast('cart', 'Update failed', existingItem.title);
            } finally {
                this.setCartItemPending(itemId, false);
            }
        },
        async incrementCartItem(item) {
            if (! item) {
                return;
            }

            const nextQuantity = Number(item.quantity || 0) + 1;
            const availableStock = Number(item.stock || 0);

            if (availableStock > 0 && nextQuantity > availableStock) {
                return;
            }

            await this.updateCartItemQuantity(item.id, nextQuantity);
        },
        async decrementCartItem(item) {
            if (! item) {
                return;
            }

            const nextQuantity = Number(item.quantity || 0) - 1;

            if (nextQuantity < 1) {
                return;
            }

            await this.updateCartItemQuantity(item.id, nextQuantity);
        },
        async removeCartItem(itemId) {
            if (! this.isAuthenticated || this.isCartItemPending(itemId)) {
                return;
            }

            const existingItem = this.cartItems.find(entry => String(entry.id) === String(itemId));

            if (! existingItem) {
                return;
            }

            this.setCartItemPending(itemId, true);

            try {
                const response = await fetch(this.cartItemUrl(this.cartItemDestroyUrlTemplate, itemId), {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const payload = await response.json();

                if (! response.ok) {
                    throw new Error(payload.message || 'Unable to remove cart item.');
                }

                this.cartItems = this.cartItems.filter(entry => String(entry.id) !== String(payload.item_id));
                this.pulseAction('cart');
                this.showActionToast('cart', 'Removed from cart', existingItem.title);
            } catch (error) {
                this.showActionToast('cart', 'Remove failed', existingItem.title);
            } finally {
                this.setCartItemPending(itemId, false);
            }
        },
        addWishlistItem(item) {
            if (! item || ! item.id) {
                return;
            }

            const alreadySaved = this.wishlistItems.some(entry => String(entry.id) === String(item.id));

            if (! alreadySaved) {
                this.wishlistItems.unshift({
                    id: item.id,
                    title: item.title || 'Saved item',
                    subtitle: item.subtitle || '',
                    price: Number(item.price || 0),
                    image: item.image || null,
                    url: item.url || '#',
                });

                this.persistWishlist();
            }

            this.pulseAction('wishlist');
            this.showActionToast('wishlist', alreadySaved ? 'Already saved' : 'Wishlist updated', item.title);

        },
        addCartItem(item) {
            if (! item || ! item.id) {
                return;
            }

            const existingItem = this.cartItems.find(entry => String(entry.listing_id) === String(item.listing_id));

            if (existingItem) {
                existingItem.quantity = Number(item.quantity || existingItem.quantity || 1);
                existingItem.price = Number(item.price || existingItem.price || 0);
                existingItem.stock = Number(item.stock || existingItem.stock || 0);
            } else {
                this.cartItems.unshift({
                    id: item.id,
                    listing_id: item.listing_id,
                    title: item.title || 'Cart item',
                    subtitle: item.subtitle || '',
                    quantity: Number(item.quantity || 1),
                    price: Number(item.price || 0),
                    stock: Number(item.stock || 0),
                    url: item.url || '#',
                    image: item.image || null,
                });
            }

            this.pulseAction('cart');
            this.showActionToast('cart', 'Added to cart', item.title);

        },
        removeWishlistItem(itemId) {
            this.wishlistItems = this.wishlistItems.filter(item => String(item.id) !== String(itemId));
            this.persistWishlist();
        },
        cartCount() {
            return this.cartItems.reduce((count, item) => count + Number(item.quantity || 0), 0);
        },
        cartSubtotal() {
            return this.cartItems.reduce((sum, item) => sum + (Number(item.price || 0) * Number(item.quantity || 0)), 0);
        },
        formatPrice(value) {
            return `${new Intl.NumberFormat('vi-VN').format(Number(value || 0))} VND`;
        },
    }"
    x-on:keydown.escape.window="closePanel(); accountMenuOpen = false; mobileMenuOpen = false"
    class="fixed inset-x-0 top-0 z-50 w-full border-b border-black/5 bg-[#FCF9F4]/15 backdrop-blur-md transition-colors duration-300 dark:border-white/5 dark:bg-gray-900/40"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between gap-4">
            <div class="flex lg:hidden">
                <button type="button" x-on:click="mobileMenuOpen = ! mobileMenuOpen" class="-ml-2 p-2 text-black transition-colors hover:text-[#D32F2F] dark:text-white">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>

            <div class="flex shrink-0 items-center">
                <a href="/" class="flex items-center gap-2">
                    <img class="block max-h-10 w-auto object-contain dark:hidden"
                         src="{{ Vite::asset('resources/images/logo-light-horizontal.png') }}"
                         alt="logo light" />
                </a>
            </div>

            <nav class="hidden flex-1 items-center justify-center gap-8 lg:flex">
                <a href="/" class="group flex items-center text-[15px] font-bold text-black transition-colors hover:text-[#D32F2F] dark:text-white">
                    Home
                    <svg class="ml-1.5 h-3 w-3 text-gray-400" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 0C12 6.62742 17.3726 12 24 12C17.3726 12 12 17.3726 12 24C12 17.3726 6.62742 12 0 12C6.62742 12 12 6.62742 12 0Z"/>
                    </svg>
                </a>
                <a href="{{ route('app.products.index') }}" class="group flex items-center text-[15px] font-bold text-black transition-colors hover:text-[#D32F2F] dark:text-white">
                    All Products
                    <svg class="ml-1.5 h-3 w-3 text-gray-400" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 0C12 6.62742 17.3726 12 24 12C17.3726 12 12 17.3726 12 24C12 17.3726 6.62742 12 0 12C6.62742 12 12 6.62742 12 0Z"/>
                    </svg>
                </a>
                <a href="{{ route('app.sellers.index') }}" class="group flex items-center text-[15px] font-bold text-black transition-colors hover:text-[#D32F2F] dark:text-white">
                    Browse Sellers
                    <svg class="ml-1.5 h-3 w-3 text-gray-400" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 0C12 6.62742 17.3726 12 24 12C17.3726 12 12 17.3726 12 24C12 17.3726 6.62742 12 0 12C6.62742 12 12 6.62742 12 0Z"/>
                    </svg>
                </a>
                <a href="#" class="group flex items-center text-[15px] font-bold text-black transition-colors hover:text-[#D32F2F] dark:text-white">
                    My Library
                    <svg class="ml-1.5 h-3 w-3 text-gray-400" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 0C12 6.62742 17.3726 12 24 12C17.3726 12 12 17.3726 12 24C12 17.3726 6.62742 12 0 12C6.62742 12 12 6.62742 12 0Z"/>
                    </svg>
                </a>
            </nav>

            <div class="flex items-center gap-3 sm:gap-4">
                <button type="button" x-on:click="searchOpen = ! searchOpen" class="hidden p-2 text-black transition-colors hover:text-[#D32F2F] dark:text-white sm:block">
                    <i class="fa-solid fa-magnifying-glass text-[20px]"></i>
                </button>

                <button type="button" x-on:click="openPanel('wishlist')" x-bind:class="wishlistPulse ? 'scale-110 border-[#D32F2F]/45 text-[#D32F2F] shadow-lg shadow-[#D32F2F]/20 dark:text-[#ff9c9c]' : ''" class="group relative inline-flex h-11 w-11 items-center justify-center rounded-full border border-black/8 bg-white/70 text-black shadow-sm shadow-black/5 transition-all duration-300 hover:-translate-y-0.5 hover:border-[#D32F2F]/30 hover:text-[#D32F2F] hover:shadow-lg hover:shadow-[#D32F2F]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-[#D32F2F]/40 dark:hover:text-[#ff8b8b]">
                    <i class="fa-regular fa-heart text-[19px] transition-transform duration-300" x-bind:class="wishlistPulse ? 'scale-125' : ''"></i>
                    <span x-show="wishlistItems.length > 0" x-transition.scale.origin.top.right.duration.200ms x-bind:class="wishlistPulse ? 'scale-115 ring-[#D32F2F]/15' : ''" class="absolute right-0.5 top-0.5 inline-flex min-h-[20px] min-w-[20px] items-center justify-center rounded-full bg-[#D32F2F] px-1.5 text-[10px] font-bold leading-none text-white ring-4 ring-[#FCF9F4]/70 transition-transform duration-300 dark:ring-gray-900/60" x-text="wishlistItems.length"></span>
                </button>

                <button type="button" x-on:click="openPanel('cart')" x-bind:class="cartPulse ? 'scale-110 border-[#D32F2F]/45 text-[#D32F2F] shadow-lg shadow-[#D32F2F]/20 dark:text-[#ff9c9c]' : ''" class="group relative inline-flex h-11 w-11 items-center justify-center rounded-full border border-black/8 bg-white/70 text-black shadow-sm shadow-black/5 transition-all duration-300 hover:-translate-y-0.5 hover:border-[#D32F2F]/30 hover:text-[#D32F2F] hover:shadow-lg hover:shadow-[#D32F2F]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-[#D32F2F]/40 dark:hover:text-[#ff8b8b]">
                    <i class="fa-solid fa-cart-shopping text-[19px] transition-transform duration-300" x-bind:class="cartPulse ? 'scale-125' : ''"></i>
                    <span x-show="cartCount() > 0" x-transition.scale.origin.top.right.duration.200ms x-bind:class="cartPulse ? 'scale-115 ring-[#D32F2F]/15' : ''" class="absolute right-0.5 top-0.5 inline-flex min-h-[20px] min-w-[20px] items-center justify-center rounded-full bg-[#D32F2F] px-1.5 text-[10px] font-bold leading-none text-white ring-4 ring-[#FCF9F4]/70 transition-transform duration-300 dark:ring-gray-900/60" x-text="cartCount()"></span>
                </button>

                <div class="hidden h-6 border-l border-black/10 sm:block dark:border-white/10"></div>

                <div class="relative">
                    <button type="button" x-on:click="accountMenuOpen = ! accountMenuOpen; drawerOpen = false" class="group inline-flex h-11 items-center gap-2 rounded-full border border-black/8 bg-white/70 px-3 text-black shadow-sm shadow-black/5 transition-all duration-300 hover:-translate-y-0.5 hover:border-[#D32F2F]/30 hover:text-[#D32F2F] hover:shadow-lg hover:shadow-[#D32F2F]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-[#D32F2F]/40 dark:hover:text-[#ff8b8b]">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-black text-sm text-white transition-colors group-hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:group-hover:bg-[#D32F2F] dark:group-hover:text-white">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <i class="fa-solid fa-chevron-down text-[10px] opacity-70 transition-transform duration-300" x-bind:class="accountMenuOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="accountMenuOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="translate-y-2 scale-95 opacity-0"
                         x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                         x-transition:leave-end="translate-y-2 scale-95 opacity-0"
                         x-on:click.outside="accountMenuOpen = false"
                         style="display: none;"
                         class="absolute right-0 top-full z-[60] mt-4 w-72 overflow-hidden rounded-3xl border border-black/10 bg-white/95 p-2 shadow-2xl shadow-black/15 backdrop-blur-xl dark:border-white/10 dark:bg-gray-950/95">
                        <div class="rounded-2xl bg-[#FCF9F4] px-4 py-3 dark:bg-white/5">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Account</p>
                            <p class="mt-1 text-sm font-semibold text-black dark:text-white" x-text="authName || 'Guest session'"></p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="isAuthenticated ? 'Manage your account and saved activity.' : 'Sign in to manage your cart and account.'"></p>
                        </div>

                        <div class="mt-2 space-y-1">
                            <a x-bind:href="profileUrl" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-black transition-colors hover:bg-[#FCF9F4] hover:text-[#D32F2F] dark:text-white dark:hover:bg-white/5 dark:hover:text-[#ff8b8b]">
                                <span class="flex items-center gap-3">
                                    <i class="fa-regular fa-id-badge text-[15px]"></i>
                                    My profile
                                </span>
                                <i class="fa-solid fa-arrow-right text-[11px] opacity-60"></i>
                            </a>

                            <template x-if="isAuthenticated">
                                <a x-bind:href="logoutUrl" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-500/10">
                                    <span class="flex items-center gap-3">
                                        <i class="fa-solid fa-right-from-bracket text-[15px]"></i>
                                        Logout
                                    </span>
                                    <i class="fa-solid fa-arrow-right text-[11px] opacity-60"></i>
                                </a>
                            </template>

                            <template x-if="! isAuthenticated">
                                <a x-bind:href="loginUrl" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-black transition-colors hover:bg-[#FCF9F4] hover:text-[#D32F2F] dark:text-white dark:hover:bg-white/5 dark:hover:text-[#ff8b8b]">
                                    <span class="flex items-center gap-3">
                                        <i class="fa-solid fa-right-to-bracket text-[15px]"></i>
                                        Login
                                    </span>
                                    <i class="fa-solid fa-arrow-right text-[11px] opacity-60"></i>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="actionToastOpen"
             x-transition:enter="transform transition ease-out duration-250"
             x-transition:enter-start="translate-y-2 scale-95 opacity-0"
             x-transition:enter-end="translate-y-0 scale-100 opacity-100"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 scale-100 opacity-100"
             x-transition:leave-end="translate-y-2 scale-95 opacity-0"
             style="display: none;"
             class="pointer-events-none fixed right-4 top-24 z-[85] w-[min(22rem,calc(100vw-2rem))]">
            <div x-bind:class="actionToast.type === 'wishlist' ? 'border-[#D32F2F]/15 bg-white/95 dark:border-[#D32F2F]/20 dark:bg-gray-950/95' : 'border-emerald-500/15 bg-white/95 dark:border-emerald-400/20 dark:bg-gray-950/95'" class="overflow-hidden rounded-3xl border p-3 shadow-[0_24px_60px_-30px_rgba(0,0,0,0.45)] backdrop-blur-xl">
                <div class="flex items-start gap-3">
                    <div x-bind:class="actionToast.type === 'wishlist' ? 'bg-[#D32F2F] text-white' : 'bg-emerald-500 text-white'" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl shadow-sm transition-transform duration-300">
                        <i x-bind:class="actionToast.type === 'wishlist' ? 'fa-regular fa-heart' : 'fa-solid fa-cart-shopping'"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p x-bind:class="actionToast.type === 'wishlist' ? 'text-[#D32F2F] dark:text-[#ff9c9c]' : 'text-emerald-600 dark:text-emerald-300'" class="text-[11px] font-semibold uppercase tracking-[0.18em]" x-text="actionToast.label"></p>
                        <p class="mt-1 line-clamp-1 text-sm font-semibold text-black dark:text-white" x-text="actionToast.title"></p>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="drawerOpen" x-transition.opacity.duration.300ms style="display: none;" class="fixed inset-0 z-[80]">
            <div class="absolute inset-0 bg-black/45 backdrop-blur-[2px]" x-on:click="closePanel()"></div>

            <div class="absolute inset-y-0 right-0 flex max-w-full pl-10">
                <section x-show="drawerOpen"
                         x-transition:enter="transform transition ease-out duration-500"
                         x-transition:enter-start="translate-x-full opacity-0"
                         x-transition:enter-end="translate-x-0 opacity-100"
                         x-transition:leave="transform transition ease-in duration-300"
                         x-transition:leave-start="translate-x-0 opacity-100"
                         x-transition:leave-end="translate-x-full opacity-0"
                         style="display: none;"
                         class="flex h-full w-screen max-w-md flex-col overflow-hidden border-l border-black/10 bg-white/95 shadow-2xl shadow-black/20 backdrop-blur-2xl dark:border-white/10 dark:bg-gray-950/95">
                <div class="border-b border-black/8 px-5 pb-4 pt-5 dark:border-white/10">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Quick Access</p>
                            <h2 class="mt-2 text-xl font-semibold text-black dark:text-white" x-text="activeDrawerTab === 'cart' ? 'Your cart' : 'Your wishlist'"></h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="activeDrawerTab === 'cart' ? 'A focused checkout-ready panel on the edge of the screen.' : 'Saved picks that follow the user through a simple cookie.'"></p>
                        </div>

                        <button type="button" x-on:click="closePanel()" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white/70 text-black transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="mt-4 grid grid-cols-2 rounded-2xl bg-[#F6EBD9] p-1.5 dark:bg-white/5">
                        <button type="button" x-on:click="activeDrawerTab = 'cart'" class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition-all duration-300" x-bind:class="activeDrawerTab === 'cart' ? 'bg-white text-black shadow-sm dark:bg-gray-900 dark:text-white' : 'text-gray-500 hover:text-black dark:text-gray-400 dark:hover:text-white'">
                            <i class="fa-solid fa-cart-shopping text-[13px]"></i>
                            Cart
                            <span class="rounded-full bg-black/6 px-2 py-0.5 text-[11px] dark:bg-white/10" x-text="cartCount()"></span>
                        </button>

                        <button type="button" x-on:click="activeDrawerTab = 'wishlist'" class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition-all duration-300" x-bind:class="activeDrawerTab === 'wishlist' ? 'bg-white text-black shadow-sm dark:bg-gray-900 dark:text-white' : 'text-gray-500 hover:text-black dark:text-gray-400 dark:hover:text-white'">
                            <i class="fa-regular fa-heart text-[13px]"></i>
                            Wishlist
                            <span class="rounded-full bg-black/6 px-2 py-0.5 text-[11px] dark:bg-white/10" x-text="wishlistItems.length"></span>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-5">
                    <div x-show="activeDrawerTab === 'cart'" class="space-y-4">
                        <template x-if="! isAuthenticated">
                            <div class="rounded-3xl border border-dashed border-black/15 bg-[#FCF9F4] p-6 text-center dark:border-white/10 dark:bg-white/5">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-black text-white dark:bg-white dark:text-gray-950">
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-black dark:text-white">Login required for cart</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Wishlist can stay with the visitor in cookies, but cart is reserved for signed-in accounts so checkout stays clean and traceable.</p>
                                <a href="{{ $loginUrl }}" class="mt-5 inline-flex items-center justify-center rounded-2xl bg-black px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                    Login to open cart
                                </a>
                            </div>
                        </template>

                        <template x-if="isAuthenticated && ! cartItems.length">
                            <div class="rounded-3xl border border-dashed border-black/15 bg-[#FCF9F4] p-6 text-center dark:border-white/10 dark:bg-white/5">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-black text-white dark:bg-white dark:text-gray-950">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-black dark:text-white">Your cart is empty</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Once you start adding listings, they will appear here with a quick subtotal and a cleaner path to checkout.</p>
                            </div>
                        </template>

                        <template x-if="isAuthenticated && cartItems.length">
                            <div class="space-y-3">
                                <template x-for="item in cartItems" :key="`cart-${item.id}`">
                                    <div x-bind:class="isCartItemPending(item.id) ? 'opacity-70' : ''" class="group rounded-3xl border border-black/8 bg-[#FCF9F4] p-3 transition-all duration-300 hover:-translate-y-0.5 hover:border-[#D32F2F]/20 hover:shadow-lg hover:shadow-black/5 dark:border-white/10 dark:bg-white/5">
                                        <div class="flex items-start gap-3">
                                            <a x-bind:href="item.url" class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-black/5 dark:bg-white/5">
                                                <template x-if="item.image">
                                                    <img x-bind:src="item.image" x-bind:alt="item.title" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                                </template>
                                                <template x-if="! item.image">
                                                    <div class="flex h-full items-center justify-center bg-gradient-to-br from-black to-gray-700 text-white dark:from-white dark:to-gray-300 dark:text-gray-950">
                                                        <i class="fa-solid fa-gamepad"></i>
                                                    </div>
                                                </template>
                                            </a>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <a x-bind:href="item.url" class="line-clamp-1 text-sm font-semibold text-black transition-colors hover:text-[#D32F2F] dark:text-white dark:hover:text-[#ff8b8b]" x-text="item.title"></a>
                                                        <p class="mt-1 line-clamp-1 text-xs text-gray-500 dark:text-gray-400" x-text="item.subtitle || 'Store listing'"></p>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        x-on:click="removeCartItem(item.id)"
                                                        x-bind:disabled="isCartItemPending(item.id)"
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-black/10 text-gray-500 transition-colors hover:border-rose-500/30 hover:bg-rose-500 hover:text-white disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:text-gray-300 dark:hover:border-rose-400/40 dark:hover:bg-rose-500 dark:hover:text-white">
                                                        <i class="fa-solid fa-trash-can text-[12px]"></i>
                                                    </button>
                                                </div>

                                                <div class="mt-4 flex items-end justify-between gap-3">
                                                    <div class="space-y-2">
                                                        <span class="block text-xs font-medium text-gray-500 dark:text-gray-400" x-text="item.stock > 0 ? `${item.stock} keys left` : 'Sold out'"></span>

                                                        <div class="inline-flex items-center rounded-full border border-black/10 bg-white p-1 shadow-sm dark:border-white/10 dark:bg-gray-900">
                                                            <button
                                                                type="button"
                                                                x-on:click="decrementCartItem(item)"
                                                                x-bind:disabled="isCartItemPending(item.id) || Number(item.quantity || 0) <= 1"
                                                                class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm text-black transition-colors hover:bg-[#F6EBD9] hover:text-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-40 dark:text-white dark:hover:bg-white/10 dark:hover:text-[#ff8b8b]">
                                                                <i class="fa-solid fa-minus text-[10px]"></i>
                                                            </button>

                                                            <span class="min-w-[2.5rem] text-center text-sm font-semibold text-black dark:text-white" x-text="item.quantity"></span>

                                                            <button
                                                                type="button"
                                                                x-on:click="incrementCartItem(item)"
                                                                x-bind:disabled="isCartItemPending(item.id) || Number(item.stock || 0) <= 0 || Number(item.quantity || 0) >= Number(item.stock || 0)"
                                                                class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm text-black transition-colors hover:bg-[#F6EBD9] hover:text-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-40 dark:text-white dark:hover:bg-white/10 dark:hover:text-[#ff8b8b]">
                                                                <i class="fa-solid fa-plus text-[10px]"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="text-right">
                                                        <p class="text-[11px] font-medium uppercase tracking-[0.14em] text-gray-400 dark:text-gray-500">Total</p>
                                                        <span class="mt-1 block text-sm font-semibold text-black dark:text-white" x-text="formatPrice(item.price * item.quantity)"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div x-show="activeDrawerTab === 'wishlist'" class="space-y-4">
                        <template x-if="! wishlistItems.length">
                            <div class="rounded-3xl border border-dashed border-black/15 bg-[#FCF9F4] p-6 text-center dark:border-white/10 dark:bg-white/5">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#D32F2F] text-white">
                                    <i class="fa-regular fa-heart"></i>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-black dark:text-white">Wishlist is empty</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Saved items will live in a browser cookie for now, so guests can still keep a shortlist before signing in.</p>
                            </div>
                        </template>

                        <template x-if="wishlistItems.length">
                            <div class="space-y-3">
                                <template x-for="item in wishlistItems" :key="`wishlist-${item.id}`">
                                    <div class="rounded-3xl border border-black/8 bg-[#FCF9F4] p-3 transition-all duration-300 hover:-translate-y-0.5 hover:border-[#D32F2F]/20 hover:shadow-lg hover:shadow-black/5 dark:border-white/10 dark:bg-white/5">
                                        <div class="flex items-start gap-3">
                                            <a x-bind:href="item.url" class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-black/5 dark:bg-white/5">
                                                <template x-if="item.image">
                                                    <img x-bind:src="item.image" x-bind:alt="item.title" class="h-full w-full object-cover">
                                                </template>
                                                <template x-if="! item.image">
                                                    <div class="flex h-full items-center justify-center bg-gradient-to-br from-[#D32F2F] to-black text-white dark:from-[#D32F2F] dark:to-white dark:text-gray-950">
                                                        <i class="fa-solid fa-star"></i>
                                                    </div>
                                                </template>
                                            </a>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <a x-bind:href="item.url" class="line-clamp-1 text-sm font-semibold text-black transition-colors hover:text-[#D32F2F] dark:text-white dark:hover:text-[#ff8b8b]" x-text="item.title"></a>
                                                        <p class="mt-1 line-clamp-1 text-xs text-gray-500 dark:text-gray-400" x-text="item.subtitle || 'Saved for later'"></p>
                                                    </div>
                                                    <button type="button" x-on:click="removeWishlistItem(item.id)" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-black/10 text-gray-500 transition-colors hover:border-[#D32F2F]/30 hover:bg-[#D32F2F] hover:text-white dark:border-white/10 dark:text-gray-300 dark:hover:border-[#D32F2F]/40 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                                        <i class="fa-solid fa-xmark text-[12px]"></i>
                                                    </button>
                                                </div>

                                                <div class="mt-4 flex items-center justify-end gap-3">
                                                    <span class="text-sm font-semibold text-black dark:text-white" x-text="formatPrice(item.price)"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="border-t border-black/8 px-5 py-4 dark:border-white/10">
                    <div x-show="activeDrawerTab === 'cart'" class="space-y-4">
                        <template x-if="isAuthenticated && cartItems.length">
                            <div class="rounded-3xl bg-[#F6EBD9] px-4 py-4 dark:bg-white/5">
                                <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                    <span>Subtotal</span>
                                    <span class="font-semibold text-black dark:text-white" x-text="formatPrice(cartSubtotal())"></span>
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-3">
                                    <button type="button" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm font-semibold text-black transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-900 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                                        Review cart
                                    </button>
                                    <button type="button" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                        Checkout soon
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="activeDrawerTab === 'wishlist'" class="space-y-3">
                        <button type="button" x-on:click="openPanel('cart')" class="inline-flex w-full items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm font-semibold text-black transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-900 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                            Switch to cart
                        </button>
                    </div>
                </div>
                </section>
            </div>
        </div>
    </template>
</header>
