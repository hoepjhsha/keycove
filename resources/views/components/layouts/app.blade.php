<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <title>{{ $title ?? '' }} - @yield('pre-app-name') {{ config('app.name') }}</title>

        <link rel="shortcut icon" href="{{ public_path('favicon.ico') }}" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles

        @stack('styles')
    </head>
    <body>
        {{ $slot }}

        @livewireScripts

        @stack('scripts')
    </body>
</html>
