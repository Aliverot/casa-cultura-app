<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Casa de la Cultura de Cuilápam de Guerrero - Catálogo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-gray-200 p-4 rounded-lg flex justify-center space-x-8 mb-6 shadow-sm">
                <a href="#" class="text-gray-700 font-bold hover:text-blue-600">Catálogo</a>
                <a href="#" class="bg-gray-800 text-white px-4 py-1 rounded-md shadow">Instrumentos Nuevos</a>
                <a href="#" class="text-gray-700 font-bold hover:text-blue-600">Préstamos</a>
                <a href="#" class="text-gray-700 font-bold hover:text-blue-600">Mantenimiento</a>
            </div>

            <div class="mb-6 flex justify-center">
                <input type="text" placeholder="Buscar instrumento..." class="w-1/2 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>

            <div class="space-y-4">
                @foreach($instrumentos as $item)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex justify-between items-center p-6 border-l-4 {{ $item->estado_actual == 'Disponible' ? 'border-green-500' : 'border-red-500' }}">
                        
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $item->nombre }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Categoría: {{ $item->categoria }} | QR: {{ $item->codigo_qr }}</p>
                            <p class="text-md font-semibold mt-2">
                                <span class="text-gray-800 dark:text-gray-200">Estado:</span> 
                                <span class="{{ $item->estado_actual == 'Disponible' ? 'text-green-500 font-bold' : 'text-red-500 font-bold' }}">
                                    {{ $item->estado_actual }}
                                </span>
                            </p>
                        </div>

                        <div>
                            @if($item->estado_actual == 'Disponible')
                                <form action="{{ route('prestamos.store') }}" method="POST" class="flex items-center space-x-4">
                                    @csrf 
                                    <input type="hidden" name="id_activo" value="{{ $item->id_activo }}">
                                    <input type="hidden" name="id_usuario" value="{{ auth()->user()->id_usuario }}">

                                    <div class="flex flex-col">
                                        <label class="text-xs text-gray-600 dark:text-gray-400 mb-1">Devolución prevista:</label>
                                        <input type="datetime-local" name="fecha_devolucion_prevista" required class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm h-9 focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-md shadow transition duration-150 ease-in-out">
                                        Prestar
                                    </button>
                                </form>
                            @else
                                <span class="bg-red-600 text-white font-bold py-2 px-6 rounded-md shadow cursor-not-allowed">
                                    En Uso / Mantenimiento
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>