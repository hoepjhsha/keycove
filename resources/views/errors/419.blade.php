<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') }"
      x-init="$watch('theme', val => localStorage.setItem('theme', val))"
      x-bind:data-theme="theme"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <title>419 - Phiên đã hết hạn</title>

        @vite(['resources/css/app.css'])
        <style>
            @keyframes spin-slow {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .animate-spin-slow {
                animation: spin-slow 3s linear infinite;
            }
        </style>
    </head>
    <body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased font-sans">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="w-full max-w-md text-center">
                <!-- Large Error Code -->
                <div class="mb-8">
                    <span class="text-9xl md:text-[120px] font-black bg-gradient-to-br from-purple-500 via-purple-600 to-purple-700 bg-clip-text text-transparent">
                        419
                    </span>
                </div>

                <!-- Icon -->
                <div class="mb-8 flex justify-center">
                    <svg class="w-20 h-20 text-purple-500 dark:text-purple-400 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <!-- Title -->
                <h1 class="text-3xl md:text-4xl font-bold mb-4">Phiên đã hết hạn</h1>

                <!-- Description -->
                <p class="text-gray-600 dark:text-gray-400 text-base md:text-lg mb-8">
                    Phiên của bạn đã hết hạn do không hoạt động. Vui lòng tải lại trang hoặc đăng nhập lại để tiếp tục.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button onclick="window.location.reload()" class="inline-flex items-center justify-center px-6 py-3 rounded-lg font-semibold text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 transition-colors duration-200">
                        Tải lại trang
                    </button>
                    <a href="{{ route('app.shop.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-lg font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                        Về trang chủ
                    </a>
                </div>

                <!-- Footer Message -->
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-12">
                    Mã lỗi: 419
                </p>
            </div>
        </div>
    </body>
</html>
