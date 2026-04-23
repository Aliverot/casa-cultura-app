<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Control - Casa de la Cultura') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white p-4 rounded-lg flex flex-wrap justify-center gap-4 mb-8 shadow-sm border border-gray-200">
                <a href="{{ route('catalogo') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Catálogo</a>
                <a href="{{ route('activos.create') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Instrumentos Nuevos</a>
                <a href="{{ route('prestamos.activos') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Devoluciones / Multas</a>
                <a href="{{ route('mantenimiento.index') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Mantenimiento</a>
                <a href="{{ route('prestamos.historial') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Historial (Log)</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div class="bg-white p-6 rounded-3xl shadow-lg border-b-4 border-blue-500 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Total Activos</p>
                    <p class="text-5xl font-black text-gray-900 mt-3">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-lg border-b-4 border-green-500 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Disponibles</p>
                    <p class="text-5xl font-black text-gray-900 mt-3">{{ $stats['disponibles'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-lg border-b-4 border-yellow-500 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">En Préstamo</p>
                    <p class="text-5xl font-black text-gray-900 mt-3">{{ $stats['prestados'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-lg border-b-4 border-red-500 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Mantenimiento</p>
                    <p class="text-5xl font-black text-gray-900 mt-3">{{ $stats['mantenimiento'] }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl p-8 border border-gray-200">
                <h3 class="text-2xl font-black mb-6 text-gray-900 border-b border-gray-100 pb-4 uppercase tracking-tighter">Acciones Operativas</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="{{ route('catalogo') }}" class="flex flex-col items-center p-6 bg-blue-50 rounded-2xl border border-blue-100 hover:bg-blue-100 hover:scale-105 hover:shadow-md transition transform text-center">
                        <span class="text-4xl mb-3">🎸</span>
                        <p class="font-black text-blue-900 uppercase tracking-widest text-sm">Catálogo</p>
                        <p class="text-xs text-blue-700 mt-1">Prestar instrumentos</p>
                    </a>

                    <a href="{{ route('activos.create') }}" class="flex flex-col items-center p-6 bg-green-50 rounded-2xl border border-green-100 hover:bg-green-100 hover:scale-105 hover:shadow-md transition transform text-center">
                        <span class="text-4xl mb-3">➕</span>
                        <p class="font-black text-green-900 uppercase tracking-widest text-sm">Nuevo Ingreso</p>
                        <p class="text-xs text-green-700 mt-1">Registrar en inventario</p>
                    </a>

                    <a href="{{ route('prestamos.activos') }}" class="flex flex-col items-center p-6 bg-purple-50 rounded-2xl border border-purple-100 hover:bg-purple-100 hover:scale-105 hover:shadow-md transition transform text-center">
                        <span class="text-4xl mb-3">🔄</span>
                        <p class="font-black text-purple-900 uppercase tracking-widest text-sm">Devoluciones</p>
                        <p class="text-xs text-purple-700 mt-1">Recibir y cobrar multas</p>
                    </a>

                    <a href="{{ route('prestamos.historial') }}" class="flex flex-col items-center p-6 bg-gray-50 rounded-2xl border border-gray-200 hover:bg-gray-100 hover:scale-105 hover:shadow-md transition transform text-center">
                        <span class="text-4xl mb-3">📂</span>
                        <p class="font-black text-gray-800 uppercase tracking-widest text-sm">Bitácora Log</p>
                        <p class="text-xs text-gray-500 mt-1">Ver historial y reportes</p>
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>