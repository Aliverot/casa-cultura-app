<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Casa de la Cultura - Cuilápam</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-100 dark:bg-gray-900">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center dark:bg-dots-lighter selection:bg-red-500 selection:text-white">
            
            @if (Route::has('login'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500 text-lg">Panel de control</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500 text-lg">Ingresar</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500 text-lg">Registrarse</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="max-w-7xl mx-auto p-6 lg:p-8 text-center">
                <div class="flex justify-center">
                    <img src="{{ asset('img/logo.png') }}" class="w-32 h-32 rounded-full shadow-2xl border-4 border-white dark:border-gray-700" alt="Logo Casa de la Cultura">
                </div>

                <div class="mt-8">
                    <h1 class="text-4xl font-black text-gray-900 dark:text-white sm:text-6xl tracking-tight">
                        Sistema de control de activos
                    </h1>
                    <p class="mt-4 text-xl text-gray-600 dark:text-gray-400 font-medium italic">
                        Casa de la Cultura de Cuilápam de Guerrero
                    </p>
                </div>

                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="{{ route('login') }}" class="rounded-xl bg-blue-600 px-10 py-4 text-xl font-black text-white shadow-xl hover:bg-blue-500 transition transform hover:scale-110 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 uppercase tracking-widest">
                        Acceder al sistema
                    </a>
                </div>

                <div class="mt-16 text-sm text-gray-500 dark:text-gray-500 font-bold">
                    Desarrollado por Equipo 6 &copy; {{ date('Y') }}
                </div>
            </div>
        </div>
    </body>
</html>
