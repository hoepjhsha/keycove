<x-layouts.app :title="$title ?? 'Authentication'">
    <div class="relative min-h-screen flex items-center justify-center bg-cover bg-center bg-no-repeat"
         style="background-image: url('https://wallpapercave.com/wp/wp14802192.webp');">

        <div class="absolute inset-0 bg-black/20 dark:bg-black/50 transition-colors duration-300"></div>

        <button type="button" @click="theme = theme === 'light' ? 'dark' : 'light'"
                class="absolute top-6 left-6 z-20 w-10 h-10 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-full text-gray-600 dark:text-gray-300 shadow-sm hover:scale-105 transition-transform">
            <i x-show="theme === 'dark'" style="display: none;" class="fa-solid fa-sun text-lg"></i>
            <i x-show="theme === 'light'" class="fa-solid fa-moon text-lg"></i>
        </button>

        <div class="relative z-10 w-full max-w-[440px] p-8 sm:p-10 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl mx-4">

            {{ $slot }}

        </div>
    </div>
</x-layouts.app>
