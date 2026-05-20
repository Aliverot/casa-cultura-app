<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestion de Mantenimiento') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-100 py-12">
        <div class="module-page-shell">
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
                <section class="module-card">
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

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Costo del servicio</label>
                                <input
                                    type="number"
                                    name="costo_servicio"
                                    value="{{ old('costo_servicio', 0) }}"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-xl border-gray-300 bg-white p-4 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Estado resultante</label>
                                <select name="estado_condicion" required class="w-full rounded-xl border-gray-300 bg-white p-4 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500">
                                    @foreach ($estadosCondicion as $estadoCondicion)
                                        <option value="{{ $estadoCondicion }}" @selected(old('estado_condicion', 'Excelente') === $estadoCondicion)>
                                            {{ $estadoCondicion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
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

                        <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <input type="checkbox" name="es_preventivo" value="1" class="rounded border-gray-300 text-cultura-600 focus:ring-cultura-500" @checked(old('es_preventivo'))>
                            <span class="text-sm text-gray-700">Mantenimiento preventivo por temporada o alta demanda.</span>
                        </label>

                        <button type="submit" class="w-full rounded-xl bg-cultura-600 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg transition hover:bg-cultura-700">
                            Finalizar mantenimiento
                        </button>
                    </form>
                </section>

                <section class="space-y-8">
                    <div class="module-card">
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
                                    $ultimoIncidente = $item->detallesPrestamo->sortByDesc('fecha_devolucion_real')->first();
                                @endphp
                                <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-lg font-black text-red-800">{{ $item->nombre }}</p>
                                            <p class="mt-1 text-sm font-bold text-red-700">Estado: {{ $item->estado_actual }}</p>
                                            <p class="mt-1 text-xs font-bold uppercase tracking-widest text-red-600">Condicion: {{ $item->estado_condicion }}</p>
                                        </div>
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-black uppercase tracking-widest text-red-700 shadow-sm">
                                            {{ number_format($item->horas_uso, 2) }} h
                                        </span>
                                    </div>

                                    @if ($ultimoIncidente)
                                        <div class="mt-4 rounded-xl border border-red-100 bg-white p-4">
                                            <p class="text-xs font-black uppercase tracking-widest text-gray-400">Motivo de reparacion</p>
                                            <p class="mt-2 text-sm font-bold text-gray-900">{{ $ultimoIncidente->prestamo?->condiciones_devolucion ?: 'Sin condiciones de retorno registradas' }}</p>
                                            <div class="mt-3 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                                                <div>
                                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Contexto</p>
                                                    <p class="mt-1 text-gray-700">{{ $ultimoIncidente->contexto_incidente ?: 'Sin contexto registrado' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Entorno</p>
                                                    <p class="mt-1 text-gray-700">{{ $ultimoIncidente->entorno_uso ?: 'Sin entorno registrado' }}</p>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Accesorios de proteccion</p>
                                                    <p class="mt-1 text-gray-700">{{ $ultimoIncidente->accesorios_proteccion ?: 'Sin accesorios registrados' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($ultimoServicio)
                                        <div class="mt-4 rounded-xl border border-red-100 bg-white p-4">
                                            <p class="text-xs font-black uppercase tracking-widest text-gray-400">Ultima nota registrada</p>
                                            <p class="mt-2 text-sm font-bold text-gray-900">{{ $ultimoServicio->tipo }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Costo: ${{ number_format((float) $ultimoServicio->costo_servicio, 2) }}</p>
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

                    <div class="module-card">
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

            <section class="module-card mt-8">
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
                                <th class="pb-4">Costo</th>
                                <th class="pb-4">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($historialMantenimiento as $registro)
                                <tr>
                                    <td class="py-4 text-sm text-gray-600">{{ $registro->fecha_servicio->format('d/m/Y H:i') }}</td>
                                    <td class="py-4 font-bold text-gray-900">{{ $registro->activo?->nombre ?? 'Instrumento eliminado' }}</td>
                                    <td class="py-4 text-sm font-semibold text-gray-800">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span>{{ $registro->tipo }}</span>
                                            @if ($registro->es_preventivo)
                                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-green-700">Preventivo</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 text-sm text-gray-600">${{ number_format((float) $registro->costo_servicio, 2) }}</td>
                                    <td class="py-4 text-sm text-gray-600">{{ $registro->observaciones ?: 'Sin observaciones' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-gray-400">Todavia no hay mantenimientos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="module-card mt-8">
                <h3 class="border-b border-gray-100 pb-4 text-2xl font-black uppercase tracking-tight text-gray-900">
                    Informe de Baja y Adquisicion
                </h3>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b-2 border-gray-100 text-xs uppercase tracking-widest text-gray-400">
                                <th class="pb-4">Modelo / Referencia</th>
                                <th class="pb-4">Categoria</th>
                                <th class="pb-4">Costo acumulado</th>
                                <th class="pb-4">% vs valor original</th>
                                <th class="pb-4">Fallas recientes</th>
                                <th class="pb-4">Dictamen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($informesBajaAdquisicion as $informe)
                                @php
                                    $datos = is_array($informe->datos) ? $informe->datos : [];
                                    $valorOriginal = (float) ($datos['valor_original'] ?? 0);
                                    $costoTotal = (float) ($datos['costo_reparaciones'] ?? 0);
                                    $porcentajeCosto = $valorOriginal > 0 ? ($costoTotal / $valorOriginal) * 100 : 0;
                                    $preventivos = (int) ($datos['mantenimientos_preventivos'] ?? 0);
                                @endphp
                                <tr>
                                    <td class="py-4 font-bold text-gray-900">{{ $datos['referencia_modelo'] ?? ($informe->activo?->modelo ?: $informe->activo?->nombre ?? 'Sin referencia') }}</td>
                                    <td class="py-4 text-sm text-gray-600">{{ $informe->activo?->categoria ?? 'Sin categoria' }}</td>
                                    <td class="py-4 text-sm text-gray-600">
                                        <p class="font-black text-gray-900">${{ number_format($costoTotal, 2) }}</p>
                                        <p class="text-xs text-gray-500">Preventivo: ${{ number_format((float) ($datos['costo_mantenimientos_preventivos'] ?? 0), 2) }} · {{ $preventivos }} servicios</p>
                                    </td>
                                    <td class="py-4 text-sm font-black text-gray-900">{{ number_format($porcentajeCosto, 1) }}%</td>
                                    <td class="py-4 text-sm text-gray-600">{{ $datos['fallas'] ?? 0 }} en {{ $datos['periodo_dias'] ?? 365 }} dias</td>
                                    <td class="py-4 text-sm text-gray-700">{{ $informe->descripcion }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-gray-400">No hay modelos que ameriten reemplazo con los criterios actuales.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
