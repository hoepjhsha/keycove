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

        <title>{{ $__status ?? 'Error' }}</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased font-sans">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="w-full max-w-md text-center">
                <!-- Large Error Code -->
                <div class="mb-8">
                    <span class="text-9xl md:text-[120px] font-black bg-gradient-to-br from-slate-500 via-slate-600 to-slate-700 bg-clip-text text-transparent">
                        {{ $__status ?? 500 }}
                    </span>
                </div>

                <!-- Icon -->
                <div class="mb-8 flex justify-center">
                    <svg class="w-20 h-20 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4v2m0 0v2m0-12h.01M12 3a9 9 0 110 18 9 9 0 010-18z" />
                    </svg>
                </div>

                <!-- Title -->
                <h1 class="text-3xl md:text-4xl font-bold mb-4">
                    @if ($__status === 400)
                        Bad Request
                    @elseif ($__status === 401)
                        Unauthorized
                    @elseif ($__status === 503)
                        Service Unavailable
                    @else
                        Error Occurred
                    @endif
                </h1>

                <!-- Description -->
                <p class="text-gray-600 dark:text-gray-400 text-base md:text-lg mb-8">
                    {{ $message ?? 'Sorry, an error occurred while processing your request. Please try again later.' }}
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="/" class="inline-flex items-center justify-center px-6 py-3 rounded-lg font-semibold text-white bg-slate-600 hover:bg-slate-700 dark:bg-slate-500 dark:hover:bg-slate-600 transition-colors duration-200">
                        Go Home
                    </a>
                    <button onclick="window.history.back()" class="inline-flex items-center justify-center px-6 py-3 rounded-lg font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                        Go Back
                    </button>
                </div>

                <!-- Footer Message -->
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-12">
                    Error Code: {{ $__status ?? 500 }}
                </p>
            </div>
        </div>
    </body>
</html>
