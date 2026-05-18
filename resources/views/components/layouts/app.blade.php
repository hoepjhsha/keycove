<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" class="scheme-light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <meta name="color-scheme" content="only light">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <title>{{ $title ?? '' }} - @yield('pre-app-name') {{ config('app.name') }}</title>

        <link rel="shortcut icon" href="{{ Vite::asset('resources/images/favicon.ico') }}" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles

        @stack('styles')
    </head>
    <body class="bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100 antialiased font-sans">
        {{ $slot }}

{{--        <livewire:ai.chatbot />--}}

        @livewireScripts

        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.directive('tooltip', (el, { expression }) => {
                    tippy(el, {
                        content: expression,
                        duration: 0,
                        placement: 'top',
                        animation: 'shift-away',
                    });
                });
            });
        </script>

        @stack('scripts')
    </body>
</html>
