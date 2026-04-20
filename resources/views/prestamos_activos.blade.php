<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Casa de la Cultura - Préstamos Activos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-gray-200 dark:bg-gray-700 p-4 rounded-lg flex justify-center space-x-8 mb-6 shadow-sm">
                <a href="{{ route('catalogo') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Catálogo</a>
                <a href="{{ route('activos.create') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Instrumentos Nuevos</a>
                <a href="{{ route('prestamos.activos') }}" class="bg-gray-800 dark:bg-gray-900 text-white px-4 py-1 rounded-md shadow">Préstamos</a>
                <a href="{{ route('mantenimiento.index') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Mantenimiento</a>
            </div>

            <div class="space-y-4">
                @if($prestamosActivos->isEmpty())
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm text-center">
                        <p class="text-gray-500 dark:text-gray-400">Todo el inventario está en bodega. No hay préstamos activos.</p>
                    </div>
                @endif

                @foreach($prestamosActivos as $detalle)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500 flex justify-between items-center">
                        
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $detalle->activo->nombre }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Prestado a: <span class="font-bold">{{ $detalle->prestamo->usuario->name }}</span></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Fecha de salida: {{ \Carbon\Carbon::parse($detalle->prestamo->fecha_salida)->format('d/m/Y h:i A') }}</p>
                        </div>

                        <div>
                            <form action="{{ route('prestamos.devolver', $detalle->id_detalle) }}" method="POST" class="flex items-center space-x-4">
                                @csrf 
                                
                                <div class="flex flex-col">
                                    <label class="text-xs text-gray-600 dark:text-gray-400 mb-1">Estado al recibir:</label>
                                    <select name="estado_retorno" required class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm h-9 focus:ring-green-500 focus:border-green-500">
                                        <option value="Buen estado">Buen estado</option>
                                        <option value="Desgaste menor">Desgaste menor</option>
                                        <option value="Dañado">Dañado (Requiere Mantenimiento)</option>
                                    </select>
                                </div>

                                <button type="submit" class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-6 rounded-md shadow transition duration-150 ease-in-out">
                                    Devolver
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>