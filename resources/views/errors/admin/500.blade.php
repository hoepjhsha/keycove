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

        <title>500 - Server Error</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased font-sans">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
            <div class="w-full max-w-md">
                <!-- Logo/Header -->
                <div class="text-center mb-12">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Admin Panel</h1>
                </div>

                <!-- Error Box -->
                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-lg p-8 text-center">
                    <!-- Error Code -->
                    <div class="mb-6">
                        <span class="text-6xl font-black text-orange-600 dark:text-orange-400">500</span>
                    </div>

                    <!-- Error Title -->
                    <h2 class="text-2xl font-bold mb-3 text-slate-900 dark:text-white">
                        Server Error
                    </h2>

                    <!-- Error Description -->
                    <p class="text-slate-600 dark:text-slate-400 mb-8">
                        Something went wrong on our server. Our team has been notified.
                    </p>

                    <!-- Divider -->
                    <div class="h-px bg-slate-200 dark:bg-slate-700 mb-8"></div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-3">
                        <a href="/admin" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-md font-semibold text-white bg-orange-600 hover:bg-orange-700 dark:bg-orange-500 dark:hover:bg-orange-600 transition-colors duration-200">
                            Back to Dashboard
                        </a>
                        <button onclick="window.location.reload()" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-md font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors duration-200">
                            Retry
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <p class="text-center text-sm text-slate-500 dark:text-slate-500 mt-8">
                    Error 500 - Server Error
                </p>
            </div>
        </div>
    </body>
</html>
