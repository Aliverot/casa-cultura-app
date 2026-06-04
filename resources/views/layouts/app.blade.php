<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CulturaGest') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-hueso-100 dark:bg-anil-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b-2 border-cantera-500 bg-hueso-50 shadow">
                    <div class="module-page-shell py-8 [&_h2]:text-3xl [&_h2]:font-bold [&_h2]:leading-tight [&_h2]:tracking-tight [&_h2]:text-anil-900 sm:[&_h2]:text-4xl">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- TAREA: Mensajes de Confirmación (UI/UX) -->
            <!-- Este bloque detecta si el controlador envió un mensaje de 'success' -->
            @if (session('success'))
                <div class="module-page-shell mt-4">
                    <div class="bg-cantera-700 border-l-4 border-ocre-400 text-hueso-50 p-4 rounded shadow-lg flex justify-between items-center transition-all duration-500">
                        <div class="flex items-center">
                            <!-- Icono de check para UX -->
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <p class="font-bold">{{ session('success') }}</p>
                        </div>
                        <!-- Botón para cerrar la alerta -->
                        <button onclick="this.parentElement.parentElement.remove()" class="text-hueso-50 hover:text-gray-200 font-bold text-xl">&times;</button>
                    </div>
                </div>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
