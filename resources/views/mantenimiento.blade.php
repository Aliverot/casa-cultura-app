<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Mantenimiento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-200 dark:bg-gray-700 p-4 rounded-lg flex justify-center space-x-8 mb-8 shadow-sm">
                <a href="{{ route('catalogo') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Catálogo</a>
                <a href="{{ route('activos.create') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Instrumentos Nuevos</a>
                <a href="{{ route('prestamos.activos') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Préstamos</a>
                <a href="{{ route('mantenimiento.index') }}" class="bg-gray-800 dark:bg-gray-900 text-white px-4 py-1 rounded-md shadow">Mantenimiento</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-xl border dark:border-gray-700">
                    <h3 class="text-2xl font-black mb-6 text-gray-900 dark:text-white border-b dark:border-gray-700 pb-4 uppercase tracking-tighter">Registrar Servicio</h3>
                    
                    <form action="{{ route('mantenimiento.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-300 uppercase mb-2">Instrumento:</label>
                            <select name="id_activo" required class="w-full rounded-xl border-gray-300 dark:border-gray-500 dark:bg-gray-700 dark:text-white text-lg p-4 focus:ring-blue-500">
                                <option value="">-- Seleccione --</option>
                                @foreach($activosCandidatos as $item)
                                    <option value="{{ $item->id_activo }}">{{ $item->nombre }} ({{ $item->estado_actual }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-300 uppercase mb-2">Tipo de Servicio:</label>
                            <select name="tipo" required class="w-full rounded-xl border-gray-300 dark:border-gray-500 dark:bg-gray-700 dark:text-white text-lg p-4 focus:ring-blue-500">
                                <option value="Afinación">Afinación</option>
                                <option value="Limpieza">Limpieza Profunda</option>
                                <option value="Reparación">Reparación Técnica</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-300 uppercase mb-2">Observaciones:</label>
                            <textarea name="observaciones" rows="4" class="w-full rounded-xl border-gray-300 dark:border-gray-500 dark:bg-gray-700 dark:text-white text-lg p-4 focus:ring-blue-500" placeholder="Escribe aquí..."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-xl text-lg uppercase tracking-widest transition transform active:scale-95">
                            Finalizar y Liberar
                        </button>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-xl border dark:border-gray-700">
                    <h3 class="text-2xl font-black mb-6 text-gray-900 dark:text-white border-b dark:border-gray-700 pb-4 uppercase tracking-tighter">Instrumentos en Riesgo</h3>
                    <div class="space-y-4">
                        @forelse($activosCandidatos as $item)
                            <div class="p-5 border-2 rounded-2xl {{ $item->estado_actual == 'Mantenimiento' ? 'bg-red-900/20 border-red-500/50' : 'bg-yellow-900/20 border-yellow-500/50' }}">
                                <p class="text-xl font-black {{ $item->estado_actual == 'Mantenimiento' ? 'text-red-400' : 'text-yellow-400' }}">{{ $item->nombre }}</p>
                                <p class="text-sm text-gray-300 mt-1">Estado: {{ $item->estado_actual }} | Horas: {{ $item->horas_uso }}</p>
                            </div>
                        @empty
                            <div class="text-center py-10 bg-gray-50 dark:bg-gray-900 rounded-2xl border-2 border-dashed dark:border-gray-700">
                                <span class="text-5xl">✅</span>
                                <p class="text-lg text-gray-400 mt-4">Todo en orden.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>