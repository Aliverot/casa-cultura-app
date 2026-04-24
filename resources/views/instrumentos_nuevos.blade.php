<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Registrar Nuevo Instrumento / Activo') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

        <!-- Menú de Navegación Interno Corregido -->
        <div class="bg-cultura-700 p-4 rounded-lg flex justify-center space-x-8 mb-6 shadow-md">
            <!-- Corregido: activos.index -->
            <a href="{{ route('activos.index') }}" class="text-white font-bold hover:text-acento-principal transition">
                Catálogo
            </a>

            <!-- Corregido: activos.create (Botón Activo) -->
            <a href="{{ route('activos.create') }}" class="bg-acento-principal text-white px-4 py-1 rounded-md shadow-sm font-bold uppercase text-xs tracking-widest">
                Instrumentos Nuevos
            </a>

            <!-- Corregido: prestamos.activos -->
            <a href="{{ route('prestamos.activos') }}" class="text-white font-bold hover:text-acento-principal transition">
                Préstamos
            </a>

            <!-- Corregido: mantenimientos.index (con 's') -->
            <a href="{{ route('mantenimientos.index') }}" class="text-white font-bold hover:text-acento-principal transition">
                Mantenimiento
            </a>
        </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('activos.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Instrumento / Traje</label>
                        <input type="text" name="nombre" required placeholder="Ej. Guitarra Acústica Yamaha" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                        <select name="categoria" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="Instrumentos de Cuerda">Instrumentos de Cuerda</option>
                            <option value="Instrumentos de Viento">Instrumentos de Viento</option>
                            <option value="Percusiones">Percusiones</option>
                            <option value="Vestuario/Danza">Vestuario / Danza</option>
                            <option value="Equipo de Sonido">Equipo de Sonido</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Límite de horas para mantenimiento</label>
                        <input type="number" name="limite_mantenimiento" required placeholder="Ej. 100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                        <p class="text-xs text-gray-500 mt-1">Horas estimadas de uso antes de requerir afinación o limpieza profunda.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md shadow">
                            Guardar en Inventario
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
