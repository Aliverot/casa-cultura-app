<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Registrar nuevo préstamo') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="module-page-shell">
            <x-module-nav current="prestamos" />

            @if ($errors->any())
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-r shadow-sm">
                    <p class="font-black uppercase tracking-widest mb-2">No se pudo guardar el préstamo</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card overflow-hidden border-t-4 border-acento-principal">
                @if ($activos->isEmpty())
                    <div class="text-center py-16">
                        <p class="text-2xl font-black text-gray-800">No hay instrumentos disponibles en este momento.</p>
                        <p class="text-gray-500 mt-3">Revisa el inventario o procesa una devolución para liberar equipo.</p>
                        <a href="{{ route('activos.index') }}" class="inline-flex items-center mt-6 px-6 py-3 bg-cultura-600 text-white rounded-xl font-bold shadow-md hover:bg-cultura-700 transition">
                            Volver al catálogo
                        </a>
                    </div>
                @else
                    <form action="{{ route('prestamos.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-[1.35fr_0.9fr] gap-8">
                        @csrf

                        <div class="space-y-6">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Seleccionar instrumento o activo</label>
                                <select name="id_activo" required class="w-full mt-1 border-gray-300 focus:border-cultura-500 focus:ring-cultura-500 rounded-md shadow-sm">
                                    <option value="">-- Seleccione un instrumento --</option>
                                    @foreach ($activos as $activo)
                                        <option value="{{ $activo->id_activo }}" @selected(old('id_activo', $activoSeleccionado) == $activo->id_activo)>
                                            {{ $activo->nombre }} - {{ $activo->categoria }} ({{ $activo->codigo_qr }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Nombre del solicitante</label>
                                    <input
                                        type="text"
                                        name="nombre_solicitante"
                                        value="{{ old('nombre_solicitante') }}"
                                        required
                                        class="w-full mt-1 border-gray-300 focus:border-cultura-500 focus:ring-cultura-500 rounded-md shadow-sm"
                                        placeholder="Alumno, profesor o responsable"
                                    >
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Contacto o identificador</label>
                                    <input
                                        type="text"
                                        name="contacto_solicitante"
                                        value="{{ old('contacto_solicitante') }}"
                                        required
                                        class="w-full mt-1 border-gray-300 focus:border-cultura-500 focus:ring-cultura-500 rounded-md shadow-sm"
                                        placeholder="Teléfono, matrícula o control"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-2">Condiciones iniciales</label>
                                <textarea
                                    name="condiciones_entrega"
                                    rows="4"
                                    required
                                    class="w-full border-gray-300 focus:border-cultura-500 focus:ring-cultura-500 rounded-md shadow-sm p-4 text-gray-700 text-base"
                                    placeholder="Ej. En perfectas condiciones, con funda, arco completo, cuerda floja en la cuarta."
                                >{{ old('condiciones_entrega') }}</textarea>
                                <p class="mt-2 text-sm text-gray-500">Describe exactamente cómo sale el instrumento para que la devolución pueda compararse después.</p>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Fecha y hora previstas de devolución</label>
                                <input
                                    type="datetime-local"
                                    name="fecha_devolucion_prevista"
                                    value="{{ old('fecha_devolucion_prevista') }}"
                                    min="{{ now()->format('Y-m-d\TH:i') }}"
                                    required
                                    class="w-full mt-1 border-gray-300 focus:border-cultura-500 focus:ring-cultura-500 rounded-md shadow-sm"
                                >
                            </div>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded-3xl p-6 space-y-5">
                            <div>
                                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Salida automática</p>
                                <p class="text-2xl font-black text-gray-900 mt-2">{{ now()->format('d/m/Y H:i') }}</p>
                                <p class="text-sm text-gray-500 mt-2">La fecha y la hora reales se registran de forma automática al confirmar el préstamo.</p>
                            </div>

                            <div class="border-t border-gray-200 pt-5">
                                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Disponibilidad</p>
                                <p class="text-sm text-gray-700 mt-2">Al guardar, el instrumento cambiará a <span class="font-black">No disponible</span> para que no se pueda prestar dos veces.</p>
                            </div>

                            <div class="border-t border-gray-200 pt-5">
                                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Conteo de horas</p>
                                <p class="text-sm text-gray-700 mt-2">El sistema acumulará las horas reales entre la salida y la devolución, sin forzar una hora mínima.</p>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4">
                                <a href="{{ route('activos.index') }}" class="text-gray-600 hover:underline">Cancelar</a>
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-cultura-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-cultura-700 focus:outline-none focus:ring ring-cultura-300 transition shadow-lg">
                                    Confirmar préstamo
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
