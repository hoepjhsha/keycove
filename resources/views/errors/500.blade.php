<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" class="scheme-light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <meta name="color-scheme" content="only light">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <title>500 - Lỗi máy chủ</title>

        @vite(['resources/css/app.css'])
        <style>
            @keyframes pulse-glow {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.6; }
            }
            .animate-pulse-glow {
                animation: pulse-glow 2s ease-in-out infinite;
            }
        </style>
    </head>
    <body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased font-sans">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="w-full max-w-md text-center">
                <!-- Large Error Code -->
                <div class="mb-8">
                    <span class="text-9xl md:text-[120px] font-black bg-gradient-to-br from-red-500 via-red-600 to-red-700 bg-clip-text text-transparent">
                        500
                    </span>
                </div>

                <!-- Icon -->
                <div class="mb-8 flex justify-center">
                    <svg class="w-20 h-20 text-red-500 dark:text-red-400 animate-pulse-glow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4v2m0 4v2M9 3h6m0 0a1 1 0 011 1v1H8V4a1 1 0 011-1zm0 0H9m0 0a1 1 0 00-1 1v1h12V4a1 1 0 00-1-1zm0 0H3v16a2 2 0 002 2h14a2 2 0 002-2V4z" />
                    </svg>
                </div>

                <!-- Title -->
                <h1 class="text-3xl md:text-4xl font-bold mb-4">Lỗi máy chủ</h1>

                <!-- Description -->
                <p class="text-gray-600 dark:text-gray-400 text-base md:text-lg mb-8">
                    Đã có lỗi xảy ra trên máy chủ. Đội ngũ của chúng tôi đã được thông báo và đang xử lý.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('app.shop.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-lg font-semibold text-white bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 transition-colors duration-200">
                        Về trang chủ
                    </a>
                    <button onclick="window.location.reload()" class="inline-flex items-center justify-center px-6 py-3 rounded-lg font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                        Thử lại
                    </button>
                </div>

                <!-- Footer Message -->
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-12">
                    Mã lỗi: 500
                </p>
            </div>
        </div>
    </body>
</html>
