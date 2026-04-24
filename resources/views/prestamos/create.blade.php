<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Registrar Nuevo Préstamo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 border-t-4 border-acento-principal">

                <form action="{{ route('prestamos.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Selección de Instrumento -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Seleccionar Instrumento/Activo</label>
                            <select name="activo_id" class="w-full mt-1 border-gray-300 focus:border-cultura-500 focus:ring-cultura-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-100">
                                <option value="">-- Seleccione un instrumento --</option>
                                <!-- Aquí el controlador cargará los instrumentos disponibles -->
                            </select>
                        </div>

                        <!-- Nombre del Solicitante -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nombre del Solicitante (Alumno/Profesor)</label>
                            <input type="text" name="solicitante" class="w-full mt-1 border-gray-300 focus:border-cultura-500 focus:ring-cultura-500 rounded-md shadow-sm dark:bg-gray-900">
                        </div>

                        <!-- TAREA: Estética de Condiciones de Préstamo -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">Condiciones y Notas del Préstamo</label>
                            <textarea
                                name="condiciones"
                                rows="4"
                                class="w-full border-gray-300 focus:border-cultura-500 focus:ring-cultura-500 rounded-md shadow-sm dark:bg-gray-900 p-4 text-gray-700 dark:text-gray-200 text-lg"
                                placeholder="Ej: El instrumento se entrega con estuche rígido, presenta un pequeño rayón en la parte trasera..."></textarea>
                            <p class="mt-2 text-sm text-gray-500">Sea lo más detallado posible sobre el estado físico al momento de la entrega.</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('dashboard') }}" class="mr-4 text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-cultura-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-cultura-700 active:bg-cultura-900 focus:outline-none focus:border-cultura-900 focus:ring ring-cultura-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-lg">
                                {{ __('Confirmar Préstamo') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
