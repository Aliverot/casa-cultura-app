<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CulturaGest') }}</title>

        <!-- Fuentes -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts de la aplicación -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-anil-900 antialiased">
        <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-hueso-100 dark:bg-anil-900">
            <div class="absolute inset-x-0 top-0 h-3 bg-ocre-400"></div>
            <div class="absolute inset-x-0 top-3 h-24 bg-cantera-700"></div>
            <div class="absolute inset-x-0 top-24 h-3 bg-cantera-600"></div>

            <div class="relative rounded-full border-4 border-ocre-300 bg-hueso-50 p-2 shadow-xl shadow-cantera-900/15">
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-cantera-600" />
                </a>
            </div>

            <div class="relative w-full sm:max-w-md mt-6 overflow-hidden rounded-xl border border-cantera-200 bg-hueso-50 px-6 py-5 shadow-2xl shadow-cantera-900/15 dark:bg-anil-900">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
