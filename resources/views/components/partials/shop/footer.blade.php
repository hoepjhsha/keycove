<footer class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 pt-12 pb-8 sm:pt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 sm:gap-8">
            {{-- Brand & About --}}
            <div class="space-y-6">
                <a href="/" class="flex items-center gap-2">
                    <img class="block dark:hidden max-h-10 w-auto object-contain"
                         src="{{ Vite::asset('resources/images/logo-light-horizontal.png') }}"
                         alt="logo light" />
                </a>
                <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                    Premium products for your digital lifestyle. We provide high-quality keys, software, and gaming essentials at the best prices.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors"><i class="fa-brands fa-facebook-f text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors"><i class="fa-brands fa-instagram text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors"><i class="fa-brands fa-twitter text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors"><i class="fa-brands fa-youtube text-lg"></i></a>
                </div>
            </div>

            {{-- Quick Links --}}
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Quick Links</h3>
                <ul class="space-y-4">
                    <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm transition-colors">Home</a></li>
                    <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm transition-colors">Shop All</a></li>
                    <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm transition-colors">New Arrivals</a></li>
                    <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm transition-colors">Special Offers</a></li>
                </ul>
            </div>

            {{-- Support --}}
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Support</h3>
                <ul class="space-y-4">
                    <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm transition-colors">Track Order</a></li>
                    <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm transition-colors">Shipping Policy</a></li>
                    <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm transition-colors">Returns & Refunds</a></li>
                    <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm transition-colors">FAQ</a></li>
                </ul>
            </div>

            {{-- Newsletter --}}
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Stay Updated</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Subscribe to get the latest deals and new releases.</p>
                <form action="#" class="flex">
                    <input type="email" placeholder="Email Address"
                           class="flex-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-l-md py-2 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-r-md text-sm font-semibold transition-colors">
                        Join
                    </button>
                </form>
            </div>
        </div>

        {{-- Bottom Footer --}}
        <div class="mt-12 sm:mt-16 pt-8 border-t border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-4">
            <p class="text-gray-500 dark:text-gray-400 text-xs">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
            <div class="flex items-center space-x-4 grayscale opacity-60">
                <i class="fa-brands fa-cc-visa text-2xl"></i>
                <i class="fa-brands fa-cc-mastercard text-2xl"></i>
                <i class="fa-brands fa-cc-paypal text-2xl"></i>
                <i class="fa-brands fa-cc-apple-pay text-2xl"></i>
            </div>
        </div>
    </div>
</footer>
