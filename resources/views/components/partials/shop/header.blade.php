<header x-data="{ mobileMenuOpen: false, searchOpen: false }" class="relative bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
    {{-- Main Navbar --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 sm:h-20">
            {{-- Mobile Menu Button --}}
            <div class="flex lg:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 p-2">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>

            {{-- Logo --}}
            <div class="flex-shrink-0 flex items-center">
                <a href="/" class="flex items-center gap-2">
                    <img class="block dark:hidden max-h-10 w-auto object-contain"
                         src="{{ Vite::asset('resources/images/logo-light-horizontal.png') }}"
                         alt="logo light" />
                </a>
            </div>

            {{-- Desktop Navigation --}}
            <nav class="hidden lg:flex space-x-8">
                <a href="/" class="text-sm font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Home</a>
                <a href="#" class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Shop</a>
                <a href="#" class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Categories</a>
                <a href="#" class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Best Sellers</a>
                <a href="#" class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">About</a>
            </nav>

            {{-- Icons & Action --}}
            <div class="flex items-center space-x-3 sm:space-x-5">
                {{-- Search Icon (Desktop) --}}
                <button @click="searchOpen = !searchOpen" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 p-2 hidden sm:block">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </button>

                {{-- Wishlist Icon --}}
                <a href="#" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 p-2 relative">
                    <i class="fa-regular fa-heart text-lg"></i>
                    <span class="absolute top-1 right-1 bg-red-500 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center">0</span>
                </a>

                {{-- Cart Icon --}}
                <a href="#" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 p-2 relative">
                    <i class="fa-solid fa-cart-shopping text-lg"></i>
                    <span class="absolute top-1 right-1 bg-indigo-600 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center">0</span>
                </a>

                {{-- User Icon/Profile --}}
                <div class="hidden sm:block border-l border-gray-200 dark:border-gray-800 h-6 mx-2"></div>

                <a href="#" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 p-2">
                    <i class="fa-regular fa-user text-lg"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="lg:hidden bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800"
         style="display: none;">
        <div class="px-4 pt-2 pb-6 space-y-1">
            <a href="/" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800">Home</a>
            <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">Shop</a>
            <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">Categories</a>
            <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">Best Sellers</a>
            <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">About</a>
        </div>
    </div>

    {{-- Search Overlay --}}
    <div x-show="searchOpen"
         @click.away="searchOpen = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute inset-x-0 top-full bg-white dark:bg-gray-900 shadow-xl p-6 z-50 border-t border-gray-100 dark:border-gray-800"
         style="display: none;">
        <div class="max-w-3xl mx-auto">
            <form action="#" method="GET" class="relative">
                <input type="text" placeholder="Search products, brands, or categories..."
                       class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full py-3 px-6 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-indigo-600 p-2">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
    </div>
</header>
