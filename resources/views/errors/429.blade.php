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

        <title>429 - Quá nhiều yêu cầu</title>

        @vite(['resources/css/app.css'])
        <style>
            @keyframes bounce-vertical {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
            .animate-bounce-vertical {
                animation: bounce-vertical 0.8s ease-in-out infinite;
            }
        </style>
    </head>
    <body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased font-sans">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="w-full max-w-md text-center">
                <!-- Large Error Code -->
                <div class="mb-8">
                    <span class="text-9xl md:text-[120px] font-black bg-gradient-to-br from-cyan-500 via-cyan-600 to-cyan-700 bg-clip-text text-transparent">
                        429
                    </span>
                </div>

                <!-- Icon -->
                <div class="mb-8 flex justify-center">
                    <svg class="w-20 h-20 text-cyan-500 dark:text-cyan-400 animate-bounce-vertical" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>

                <!-- Title -->
                <h1 class="text-3xl md:text-4xl font-bold mb-4">Quá nhiều yêu cầu</h1>

                <!-- Description -->
                <p class="text-gray-600 dark:text-gray-400 text-base md:text-lg mb-8">
                    Bạn đã gửi quá nhiều yêu cầu trong thời gian ngắn. Vui lòng chờ một chút rồi thử lại.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button onclick="window.history.back()" class="inline-flex items-center justify-center px-6 py-3 rounded-lg font-semibold text-white bg-cyan-600 hover:bg-cyan-700 dark:bg-cyan-500 dark:hover:bg-cyan-600 transition-colors duration-200">
                        Quay lại
                    </button>
                    <a href="{{ route('app.shop.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-lg font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                        Về trang chủ
                    </a>
                </div>

                <!-- Footer Message -->
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-12">
                    Mã lỗi: 429
                </p>
            </div>
        </div>
    </body>
</html>
