<aside class="hidden md:flex flex-col w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800">
    <a href="{{ route('admin.dashboard.index') }}" class="h-16 flex justify-center items-center px-6 hover:opacity-80 transition-opacity">
        <img class="block dark:hidden max-h-10 w-auto object-contain"
             src="{{ Vite::asset('resources/images/logo-light-horizontal.png') }}"
             alt="logo light" />
        <img class="hidden dark:block max-h-10 w-auto object-contain"
             src="{{ Vite::asset('resources/images/logo-dark-horizontal.png') }}"
             alt="logo dark" />
    </a>

    <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-6">
        <ul class="space-y-1">

            <li>
                <a href="{{ route('admin.dashboard.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.dashboard.index') ? 'bg-gray-50 text-indigo-600 dark:bg-gray-800/50 dark:text-indigo-400' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }} transition-colors">
                    <i class="fa-solid fa-chart-pie text-lg w-5 text-center shrink-0"></i>
                    Dashboard
                </a>
            </li>

            <li>
                <details class="group/catalog [&_summary::-webkit-details-marker]:hidden" {{ request()->routeIs(['admin.categories.*', 'admin.attributes.regions.*', 'admin.attributes.operating_systems.*', 'admin.attributes.platforms.*', 'admin.products.keys.import', 'admin.products.index', 'admin.products.detail']) ? 'open' : '' }}>
                    <summary class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs(['admin.categories.*', 'admin.attributes.regions.*', 'admin.attributes.operating_systems.*', 'admin.attributes.platforms.*', 'admin.products.keys.import', 'admin.products.index', 'admin.products.detail']) ? 'bg-gray-50 text-indigo-600 dark:bg-gray-800/50 dark:text-indigo-400' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }} transition-colors cursor-pointer list-none">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-key text-lg w-5 text-center shrink-0"></i>
                            Catalog & Keys
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300 group-open/catalog:rotate-180"></i>
                    </summary>

                    <div class="grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out group-open/catalog:grid-rows-[1fr]">
                        <ul class="overflow-hidden flex flex-col gap-1 mt-1 pl-11 pr-3">
                            <li>
                                <a href="{{ route('admin.products.index') }}" class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.products.index') || request()->routeIs('admin.products.detail') ? 'text-indigo-600 bg-gray-50 dark:text-white dark:bg-gray-800' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800' }}">
                                    Products List
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.categories.*') ? 'text-indigo-600 bg-gray-50 dark:text-white dark:bg-gray-800' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800' }}">
                                    Categories
                                </a>
                            </li>

                            <li>
                                <details class="group/attributes [&_summary::-webkit-details-marker]:hidden" {{ request()->routeIs(['admin.attributes.regions.*', 'admin.attributes.operating_systems.*', 'admin.attributes.platforms.*']) ? 'open' : '' }}>
                                    <summary class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs(['admin.attributes.regions.*', 'admin.attributes.operating_systems.*', 'admin.attributes.platforms.*']) ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800' }} transition-colors cursor-pointer list-none">
                                        <span>Attributes</span>
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300 group-open/attributes:rotate-180"></i>
                                    </summary>

                                    <div class="grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out group-open/attributes:grid-rows-[1fr]">
                                        <ul class="overflow-hidden flex flex-col gap-1 mt-1 pl-4 border-l border-gray-200 dark:border-gray-700 ml-2">
                                            <li>
                                                <a href="{{ route('admin.attributes.regions.index') }}" class="block px-3 py-1.5 text-sm font-medium rounded-md {{ request()->routeIs('admin.attributes.regions.*') ? 'text-indigo-600 bg-gray-50 dark:text-white dark:bg-gray-800' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-800' }}">
                                                    Regions
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.attributes.operating_systems.index') }}" class="block px-3 py-1.5 text-sm font-medium rounded-md {{ request()->routeIs('admin.attributes.operating_systems.*') ? 'text-indigo-600 bg-gray-50 dark:text-white dark:bg-gray-800' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-800' }}">
                                                    Operating Systems
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.attributes.platforms.index') }}" class="block px-3 py-1.5 text-sm font-medium rounded-md {{ request()->routeIs('admin.attributes.platforms.*') ? 'text-indigo-600 bg-gray-50 dark:text-white dark:bg-gray-800' : 'text-gray-500 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-800' }}">
                                                    Platforms
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </details>
                            </li>

                            <li>
                                <a href="{{ route('admin.products.keys.import') }}" class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.products.keys.import') ? 'text-indigo-600 bg-gray-50 dark:text-white dark:bg-gray-800' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800' }}">
                                    Bulk Import Keys
                                </a>
                            </li>
                        </ul>
                    </div>
                </details>
            </li>

            <li>
                <details class="group/orders [&_summary::-webkit-details-marker]:hidden" {{ request()->routeIs(['admin.orders.*', 'admin.escrows.*']) ? 'open' : '' }}>
                    <summary class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs(['admin.orders.*', 'admin.escrows.*']) ? 'bg-gray-50 text-indigo-600 dark:bg-gray-800/50 dark:text-indigo-400' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }} transition-colors cursor-pointer list-none">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-cart-shopping text-lg w-5 text-center shrink-0"></i>
                            Orders
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300 group-open/orders:rotate-180"></i>
                    </summary>
                    <div class="grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out group-open/orders:grid-rows-[1fr]">
                        <ul class="overflow-hidden flex flex-col gap-1 mt-1 pl-11 pr-3">
                            <li><a href="{{ route('admin.orders.index') }}" class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.orders.*') ? 'text-indigo-600 bg-gray-50 dark:text-white dark:bg-gray-800' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800' }}">Orders History</a></li>
                            <li><a href="{{ route('admin.escrows.index') }}" class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.escrows.*') ? 'text-indigo-600 bg-gray-50 dark:text-white dark:bg-gray-800' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800' }}">Escrow Management</a></li>
                        </ul>
                    </div>
                </details>
            </li>

            <li>
                <details class="group/finance [&_summary::-webkit-details-marker]:hidden" {{ request()->routeIs(['admin.transactions.*']) ? 'open' : '' }}>
                    <summary class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs(['admin.transactions.*']) ? 'bg-gray-50 text-indigo-600 dark:bg-gray-800/50 dark:text-indigo-400' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }} transition-colors cursor-pointer list-none">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-money-bill-transfer text-lg w-5 text-center shrink-0"></i>
                            Finance & Wallet
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300 group-open/finance:rotate-180"></i>
                    </summary>
                    <div class="grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out group-open/finance:grid-rows-[1fr]">
                        <ul class="overflow-hidden flex flex-col gap-1 mt-1 pl-11 pr-3">
                            <li><a href="{{ route('admin.transactions.index') }}" class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.transactions.*') ? 'text-indigo-600 bg-gray-50 dark:text-white dark:bg-gray-800' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800' }}">Transactions</a></li>
                            <li><a href="#" class="block px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800">Internal Wallet</a></li>
                            <li><a href="#" class="block px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 flex justify-between items-center">Withdrawal Requests <span class="bg-red-100 text-red-600 px-2 py-0.5 rounded-full text-xs font-bold dark:bg-red-900/30 dark:text-red-400">3</span></a></li>
                        </ul>
                    </div>
                </details>
            </li>

            <li>
                <details class="group/users [&_summary::-webkit-details-marker]:hidden" {{ request()->routeIs(['admin.users.*', 'admin.seller_verifications.*']) ? 'open' : '' }}>
                    <summary class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs(['admin.users.*', 'admin.seller_verifications.*']) ? 'bg-gray-50 text-indigo-600 dark:bg-gray-800/50 dark:text-indigo-400' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }} transition-colors cursor-pointer list-none">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-users-gear text-lg w-5 text-center shrink-0"></i>
                            Users & Vendors
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300 group-open/users:rotate-180"></i>
                    </summary>
                    <div class="grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out group-open/users:grid-rows-[1fr]">
                        <ul class="overflow-hidden flex flex-col gap-1 mt-1 pl-11 pr-3">
                            <li><a href="{{ route('admin.users.index') }}" class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.users.*') ? 'text-indigo-600 bg-gray-50 dark:text-white dark:bg-gray-800' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800' }}">Users List</a></li>
                            <li><a href="{{ route('admin.seller_verifications.index') }}" class="block px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.seller_verifications.*') ? 'text-indigo-600 bg-gray-50 dark:text-white dark:bg-gray-800' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800' }}">Seller Approvals (KYC)</a></li>
                            <li><a href="#" class="block px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800">Roles & Permissions</a></li>
                        </ul>
                    </div>
                </details>
            </li>

            <li>
                <a href="{{ route('admin.complaints.index') }}" class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.complaints.*') ? 'bg-gray-50 text-indigo-600 dark:bg-gray-800/50 dark:text-indigo-400' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }} transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-scale-balanced text-lg w-5 text-center shrink-0"></i>
                        Dispute Center
                    </div>
                </a>
            </li>
        </ul>

        <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-800">
            <ul class="space-y-1">
                <li>
                    <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                        <i class="fa-solid fa-gear text-lg w-5 text-center shrink-0"></i>
                        System Settings
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="p-4 mt-auto border-t border-gray-200 dark:border-gray-800">
        <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
            <i class="fa-solid fa-user-gear text-lg w-5 text-center shrink-0"></i>
            Account Settings
        </a>

        <a href="{{ route('admin.auth.logout') }}" class="flex items-center gap-3 px-3 py-2 mt-1 text-sm font-medium rounded-md text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors">
            <i class="fa-solid fa-arrow-right-from-bracket text-lg w-5 text-center shrink-0"></i>
            Logout
        </a>
    </div>
</aside>
