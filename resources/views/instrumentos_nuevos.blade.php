<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Registrar Nuevo Instrumento / Activo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-200 dark:bg-gray-700 p-4 rounded-lg flex justify-center space-x-8 mb-6 shadow-sm">
                <a href="{{ route('catalogo') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Catálogo</a>
                <a href="{{ route('activos.create') }}" class="bg-gray-800 dark:bg-gray-900 text-white px-4 py-1 rounded-md shadow">Instrumentos Nuevos</a>
                <a href="{{ route('prestamos.activos') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Préstamos</a>
                <a href="{{ route('mantenimiento.index') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Mantenimiento</a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl p-10 border dark:border-gray-700">
                <form action="{{ route('activos.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <div>
                        <label class="block text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-2">Nombre del Activo:</label>
                        <input type="text" name="nombre" required placeholder="Ej. Guitarra Acústica Yamaha" 
                               class="block w-full rounded-xl border-gray-300 dark:border-gray-500 dark:bg-gray-700 dark:text-white text-xl p-4 shadow-inner focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-2">Categoría:</label>
                        <select name="categoria" required 
                                class="block w-full rounded-xl border-gray-300 dark:border-gray-500 dark:bg-gray-700 dark:text-white text-xl p-4 shadow-inner focus:ring-blue-500">
                            <option value="">-- Selecciona --</option>
                            <option value="Instrumentos de Cuerda">Instrumentos de Cuerda</option>
                            <option value="Instrumentos de Viento">Instrumentos de Viento</option>
                            <option value="Percusiones">Percusiones</option>
                            <option value="Vestuario/Danza">Vestuario / Danza</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-2">Límite para Mantenimiento (Horas):</label>
                        <input type="number" name="limite_mantenimiento" required placeholder="100" 
                               class="block w-full rounded-l border-gray-300 dark:border-gray-500 dark:bg-gray-700 dark:text-white text-l p-4 shadow-inner focus:ring-blue-500">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-xl text-l uppercase tracking-widest transition transform active:scale-95">
                        Guardar en Inventario
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>