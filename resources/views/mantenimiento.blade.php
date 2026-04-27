<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestion de Mantenimiento') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-100 py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-module-nav current="mantenimiento" />

            @if ($errors->any())
                <div class="mb-8 rounded-r border-l-4 border-red-500 bg-red-100 p-4 text-red-800 shadow-sm">
                    <p class="mb-2 font-black uppercase tracking-widest">No se pudo registrar el mantenimiento</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-8 xl:grid-cols-[1.05fr_0.95fr]">
                <section class="rounded-3xl border border-gray-200 bg-white p-8 shadow-xl">
                    <h3 class="border-b border-gray-100 pb-4 text-2xl font-black uppercase tracking-tight text-gray-900">
                        Finalizar Servicio y Liberar
                    </h3>

                    <form action="{{ route('mantenimientos.store') }}" method="POST" class="mt-6 space-y-6">
                        @csrf

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Instrumento</label>
                            <select name="id_activo" required class="w-full rounded-xl border-gray-300 bg-white p-4 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500">
                                <option value="">-- Seleccione un instrumento --</option>
                                @foreach ($activosCandidatos as $item)
                                    <option value="{{ $item->id_activo }}" @selected(old('id_activo') == $item->id_activo)>
                                        {{ $item->nombre }} - {{ $item->estado_actual }} - {{ number_format($item->horas_uso, 2) }} h
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Nota del mantenimiento</label>
                            <input
                                type="text"
                                name="tipo"
                                list="sugerencias-mantenimiento"
                                value="{{ old('tipo') }}"
                                required
                                placeholder="Ej. Limpieza profunda, ajuste de puente, cambio de cuerda"
                                class="w-full rounded-xl border-gray-300 bg-white p-4 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                            >
                            <datalist id="sugerencias-mantenimiento">
                                <option value="Limpieza profunda"></option>
                                <option value="Ajuste de puente"></option>
                                <option value="Cambio de cuerdas"></option>
                                <option value="Afinacion general"></option>
                                <option value="Revision electrica"></option>
                            </datalist>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Observaciones</label>
                            <textarea
                                name="observaciones"
                                rows="4"
                                class="w-full rounded-xl border-gray-300 bg-white p-4 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                                placeholder="Describe el trabajo realizado, piezas reemplazadas o cualquier observacion relevante."
                            >{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="rounded-2xl border border-cultura-100 bg-cultura-50 p-4">
                            <p class="text-xs font-black uppercase tracking-widest text-cultura-700">Finalizar y liberar</p>
                            <p class="mt-2 text-sm text-cultura-900">Al guardar, el sistema registra la nota del mantenimiento, reinicia el contador de horas de uso y vuelve a poner el instrumento como <span class="font-black">Disponible</span>.</p>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-cultura-600 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg transition hover:bg-cultura-700">
                            Finalizar mantenimiento
                        </button>
                    </form>
                </section>

                <section class="space-y-8">
                    <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-xl">
                        <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-4">
                            <h3 class="text-2xl font-black uppercase tracking-tight text-gray-900">En Reparacion</h3>
                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-red-700">
                                {{ $activosEnMantenimiento->count() }} activos
                            </span>
                        </div>

                        <div class="mt-6 space-y-4">
                            @forelse ($activosEnMantenimiento as $item)
                                @php
                                    $ultimoServicio = $item->mantenimientos->sortByDesc('fecha_servicio')->first();
                                @endphp
                                <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-lg font-black text-red-800">{{ $item->nombre }}</p>
                                            <p class="mt-1 text-sm font-bold text-red-700">Estado: {{ $item->estado_actual }}</p>
                                        </div>
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-black uppercase tracking-widest text-red-700 shadow-sm">
                                            {{ number_format($item->horas_uso, 2) }} h
                                        </span>
                                    </div>

                                    @if ($ultimoServicio)
                                        <div class="mt-4 rounded-xl border border-red-100 bg-white p-4">
                                            <p class="text-xs font-black uppercase tracking-widest text-gray-400">Ultima nota registrada</p>
                                            <p class="mt-2 text-sm font-bold text-gray-900">{{ $ultimoServicio->tipo }}</p>
                                            @if ($ultimoServicio->observaciones)
                                                <p class="mt-1 text-sm text-gray-600">{{ $ultimoServicio->observaciones }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 py-10 text-center">
                                    <p class="font-bold text-gray-500">No hay equipos en servicio tecnico en este momento.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-xl">
                        <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-4">
                            <h3 class="text-2xl font-black uppercase tracking-tight text-gray-900">Por Atender</h3>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-amber-700">
                                {{ $activosPorAtender->count() }} activos
                            </span>
                        </div>

                        <div class="mt-6 space-y-4">
                            @forelse ($activosPorAtender as $item)
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                                    <p class="text-lg font-black text-amber-800">{{ $item->nombre }}</p>
                                    <p class="mt-1 text-sm text-amber-700">
                                        Horas acumuladas: <span class="font-black">{{ number_format($item->horas_uso, 2) }}</span>
                                        de
                                        <span class="font-black">{{ number_format($item->limite_mantenimiento, 2) }}</span>
                                    </p>
                                </div>
                            @empty
                                <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 py-10 text-center">
                                    <p class="font-bold text-gray-500">No hay instrumentos pendientes por horas acumuladas.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <section class="mt-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-xl">
                <h3 class="border-b border-gray-100 pb-4 text-2xl font-black uppercase tracking-tight text-gray-900">
                    Historial Reciente de Mantenimiento
                </h3>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b-2 border-gray-100 text-xs uppercase tracking-widest text-gray-400">
                                <th class="pb-4">Fecha</th>
                                <th class="pb-4">Instrumento</th>
                                <th class="pb-4">Trabajo realizado</th>
                                <th class="pb-4">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($historialMantenimiento as $registro)
                                <tr>
                                    <td class="py-4 text-sm text-gray-600">{{ $registro->fecha_servicio->format('d/m/Y H:i') }}</td>
                                    <td class="py-4 font-bold text-gray-900">{{ $registro->activo?->nombre ?? 'Instrumento eliminado' }}</td>
                                    <td class="py-4 text-sm font-semibold text-gray-800">{{ $registro->tipo }}</td>
                                    <td class="py-4 text-sm text-gray-600">{{ $registro->observaciones ?: 'Sin observaciones' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-10 text-center text-gray-400">Todavia no hay mantenimientos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
