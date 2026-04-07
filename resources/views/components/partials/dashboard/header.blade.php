<header class="h-16 rounded-b-xl flex items-center justify-between px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 z-10 shrink-0">
    <div class="flex flex-1">
        <form class="flex w-full md:ml-0" action="#" method="GET">
            <label for="search-field" class="sr-only">Search</label>
            <div class="relative w-full text-gray-400 focus-within:text-gray-600 dark:focus-within:text-gray-300">
                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input id="search-field" class="block h-full w-full border-transparent py-2 pl-8 pr-3 text-gray-900 dark:text-gray-100 bg-transparent placeholder-gray-500 focus:border-transparent focus:placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-sm" placeholder="Search" type="search" name="search">
            </div>
        </form>
    </div>

    <div class="flex items-center gap-x-4 lg:gap-x-6 ml-4">
        <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
            <span class="sr-only">View notifications</span>
            <i class="fa-regular fa-bell text-xl"></i>
        </button>

        <button type="button" @click="theme = theme === 'light' ? 'dark' : 'light'" class="-m-2.5 p-2.5 mr-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
            <span class="sr-only">Toggle Dark Mode</span>
            <i x-show="theme === 'dark'" style="display: none;" class="fa-solid fa-sun text-xl"></i>
            <i x-show="theme === 'light'" class="fa-solid fa-moon text-xl"></i>
        </button>

        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200 dark:lg:bg-gray-700" aria-hidden="true"></div>

        <div class="relative">
            <button type="button" class="-m-1.5 flex items-center p-1.5" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                <img class="h-8 w-8 rounded-full bg-gray-50 dark:bg-gray-800 object-cover"
                     src="{{ Vite::asset('resources/images/user/avatar.png') }}"
                     alt="avatar">
                <span class="hidden lg:flex lg:items-center ml-4">
                    <span class="flex flex-col items-start text-left">
                        <span class="text-sm font-semibold leading-tight text-gray-900 dark:text-gray-200" aria-hidden="true">{{ auth()->user()?->username ?? 'Sample user' }}</span>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5" aria-hidden="true">{{ auth()->user()?->role->lablel() ?? 'Sample role' }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down ml-3 text-sm text-gray-400"></i>
                </span>
            </button>
        </div>
    </div>
</header>
