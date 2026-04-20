<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Panel de Control - Casa de la Cultura') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border-b-4 border-blue-500">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Activos</p>
                    <p class="text-4xl font-black text-gray-900 dark:text-white mt-2">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border-b-4 border-green-500">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Disponibles</p>
                    <p class="text-4xl font-black text-gray-900 dark:text-white mt-2">{{ $stats['disponibles'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border-b-4 border-yellow-500">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">En Préstamo</p>
                    <p class="text-4xl font-black text-gray-900 dark:text-white mt-2">{{ $stats['prestados'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border-b-4 border-red-500">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mantenimiento</p>
                    <p class="text-4xl font-black text-gray-900 dark:text-white mt-2">{{ $stats['mantenimiento'] }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h3 class="text-xl font-bold mb-6 text-gray-800 dark:text-white border-b pb-2 dark:border-gray-700">Acciones Operativas</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="{{ route('catalogo') }}" class="flex items-center p-6 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-800 hover:scale-105 transition transform">
                        <span class="text-3xl mr-4">🎸</span>
                        <div>
                            <p class="font-bold text-blue-900 dark:text-blue-300">Catálogo</p>
                            <p class="text-xs text-blue-700 dark:text-blue-400">Prestar instrumentos</p>
                        </div>
                    </a>

                    <a href="{{ route('activos.create') }}" class="flex items-center p-6 bg-green-50 dark:bg-green-900/20 rounded-2xl border border-green-100 dark:border-green-800 hover:scale-105 transition transform">
                        <span class="text-3xl mr-4">➕</span>
                        <div>
                            <p class="font-bold text-green-900 dark:text-green-300">Nuevo Ingreso</p>
                            <p class="text-xs text-green-700 dark:text-green-400">Registrar en inventario</p>
                        </div>
                    </a>

                    <a href="{{ route('prestamos.activos') }}" class="flex items-center p-6 bg-purple-50 dark:bg-purple-900/20 rounded-2xl border border-purple-100 dark:border-purple-800 hover:scale-105 transition transform">
                        <span class="text-3xl mr-4">🔄</span>
                        <div>
                            <p class="font-bold text-purple-900 dark:text-purple-300">Devoluciones</p>
                            <p class="text-xs text-purple-700 dark:text-purple-400">Recibir instrumentos</p>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>