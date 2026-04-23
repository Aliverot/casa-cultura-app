<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Mantenimiento') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white p-4 rounded-lg flex flex-wrap justify-center gap-4 mb-8 shadow-sm border border-gray-200">
                <a href="{{ route('catalogo') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Catálogo</a>
                <a href="{{ route('activos.create') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Instrumentos Nuevos</a>
                <a href="{{ route('prestamos.activos') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Devoluciones / Multas</a>
                <a href="{{ route('mantenimiento.index') }}" class="bg-blue-600 text-white px-6 py-2 rounded-md shadow-md font-bold">Mantenimiento</a>
                <a href="{{ route('prestamos.historial') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Historial (Log)</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-200">
                    <h3 class="text-2xl font-black mb-6 text-gray-900 border-b border-gray-100 pb-4 uppercase tracking-tighter">Registrar Servicio</h3>
                    
                    <form action="{{ route('mantenimiento.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Instrumento:</label>
                            <select name="id_activo" required class="w-full rounded-xl border-gray-300 bg-white text-gray-900 text-lg p-4 focus:ring-2 focus:ring-blue-500 shadow-sm">
                                <option value="">-- Seleccione --</option>
                                @foreach($activosCandidatos as $item)
                                    <option value="{{ $item->id_activo }}">{{ $item->nombre }} ({{ $item->estado_actual }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Tipo de Servicio:</label>
                            <select name="tipo" required class="w-full rounded-xl border-gray-300 bg-white text-gray-900 text-lg p-4 focus:ring-2 focus:ring-blue-500 shadow-sm">
                                <option value="Afinación">Afinación</option>
                                <option value="Limpieza">Limpieza Profunda</option>
                                <option value="Reparación">Reparación Técnica</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Observaciones:</label>
                            <textarea name="observaciones" rows="4" class="w-full rounded-xl border-gray-300 bg-white text-gray-900 text-lg p-4 focus:ring-2 focus:ring-blue-500 shadow-sm" placeholder="Escribe aquí..."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-lg text-lg uppercase tracking-widest transition transform active:scale-95">
                            Finalizar y Liberar
                        </button>
                    </form>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-200">
                    <h3 class="text-2xl font-black mb-6 text-gray-900 border-b border-gray-100 pb-4 uppercase tracking-tighter">Instrumentos en Riesgo</h3>
                    <div class="space-y-4">
                        @forelse($activosCandidatos as $item)
                            <div class="p-5 border-2 rounded-2xl {{ $item->estado_actual == 'Mantenimiento' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200' }}">
                                <p class="text-xl font-black {{ $item->estado_actual == 'Mantenimiento' ? 'text-red-700' : 'text-yellow-700' }}">{{ $item->nombre }}</p>
                                <p class="text-sm text-gray-600 mt-1 font-bold">Estado: {{ $item->estado_actual }} | Horas de uso: {{ $item->horas_uso }}</p>
                            </div>
                        @empty
                            <div class="text-center py-10 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                                <span class="text-5xl">✅</span>
                                <p class="text-lg text-gray-500 font-bold mt-4">Todo en orden.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>