<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nuevo Instrumento / Activo') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white p-4 rounded-lg flex flex-wrap justify-center gap-4 mb-6 shadow-sm border border-gray-200">
                <a href="{{ route('catalogo') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Catálogo</a>
                <a href="{{ route('activos.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-md shadow-md font-bold">Instrumentos Nuevos</a>
                <a href="{{ route('prestamos.activos') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Devoluciones / Multas</a>
                <a href="{{ route('mantenimiento.index') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Mantenimiento</a>
                <a href="{{ route('prestamos.historial') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Historial (Log)</a>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl p-10 border border-gray-200">
                <form action="{{ route('activos.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <div>
                        <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">Nombre del Activo:</label>
                        <input type="text" name="nombre" required placeholder="Ej. Guitarra Acústica Yamaha" 
                               class="block w-full rounded-xl border-gray-300 bg-white text-gray-900 text-xl p-4 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">Categoría:</label>
                        <select name="categoria" required class="block w-full rounded-xl border-gray-300 bg-white text-gray-900 text-xl p-4 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Selecciona --</option>
                            <option value="Instrumentos de Cuerda">Instrumentos de Cuerda</option>
                            <option value="Instrumentos de Viento">Instrumentos de Viento</option>
                            <option value="Percusiones">Percusiones</option>
                            <option value="Vestuario/Danza">Vestuario / Danza</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">Límite para Mantenimiento (Horas):</label>
                        <input type="number" name="limite_mantenimiento" required placeholder="100" 
                               class="block w-full rounded-xl border-gray-300 bg-white text-gray-900 text-xl p-4 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-lg text-xl uppercase tracking-widest transition transform active:scale-95">
                        Guardar en Inventario
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>