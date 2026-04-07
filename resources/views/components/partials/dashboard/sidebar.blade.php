<aside class="hidden md:flex flex-col w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800">
    <div class="h-16 flex justify-center items-center px-6">
        <img class="block dark:hidden max-h-10 w-auto object-contain"
             src="{{ Vite::asset('resources/images/logo-light-horizontal.png') }}"
             alt="logo light" />
        <img class="hidden dark:block max-h-10 w-auto object-contain"
             src="{{ Vite::asset('resources/images/logo-dark-horizontal.png') }}"
             alt="logo dark" />
    </div>

    <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-8">
        <ul class="space-y-1">
            <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md bg-gray-50 text-indigo-600 dark:bg-gray-800/50 dark:text-indigo-400">
                    <i class="fa-solid fa-house text-lg w-5 text-center shrink-0"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-users text-lg w-5 text-center shrink-0"></i>
                    Team
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-folder text-lg w-5 text-center shrink-0"></i>
                    Projects
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-calendar-days text-lg w-5 text-center shrink-0"></i>
                    Calendar
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-file-lines text-lg w-5 text-center shrink-0"></i>
                    Documents
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-chart-pie text-lg w-5 text-center shrink-0"></i>
                    Reports
                </a>
            </li>
        </ul>
    </nav>

    <div class="p-4 mt-auto">
        <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
            <i class="fa-solid fa-gear text-lg w-5 text-center shrink-0"></i>
            Settings
        </a>
    </div>
</aside>
