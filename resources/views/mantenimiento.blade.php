<x-app-layout>
    <div class="min-h-screen bg-hueso-100 py-12">
        <div class="module-page-shell">
            <x-module-nav current="mantenimiento" />

            @if ($errors->any())
                <div class="mb-8 rounded-r border-l-4 border-oxido-500 bg-oxido-50 p-4 text-oxido-800 shadow-sm">
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
                    <h3 class="border-b border-cantera-100 pb-4 text-2xl font-black uppercase tracking-tight text-anil-900">
                        Finalizar Servicio y Liberar
                    </h3>

                    <form action="{{ route('mantenimientos.store') }}" method="POST" class="mt-6 space-y-6">
                        @csrf

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Instrumento</label>
                            <select name="id_activo" required class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-4 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400">
                                <option value="">-- Seleccione un instrumento --</option>
                                @foreach ($activosCandidatos as $item)
                                    <option value="{{ $item->id_activo }}" @selected(old('id_activo') == $item->id_activo)>
                                        {{ $item->nombre }} - {{ $item->estado_actual }} - {{ number_format($item->horas_uso, 2) }} h
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Nota del mantenimiento</label>
                            <input
                                type="text"
                                name="tipo"
                                list="sugerencias-mantenimiento"
                                value="{{ old('tipo') }}"
                                required
                                placeholder="Ej. Limpieza profunda, ajuste de puente, cambio de cuerda"
                                class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-4 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                            >
                            <datalist id="sugerencias-mantenimiento">
                                <option value="Limpieza profunda"></option>
                                <option value="Ajuste de puente"></option>
                                <option value="Cambio de cuerdas"></option>
                                <option value="Afinación general"></option>
                                <option value="Revisión eléctrica"></option>
                            </datalist>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Costo del servicio</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-4 text-cantera-600">$</span>
                                    <input
                                        type="number"
                                        name="costo_servicio"
                                        value="{{ old('costo_servicio', 0) }}"
                                        min="0"
                                        step="0.01"
                                        class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-4 pl-9 pr-20 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                                    >
                                    <span class="absolute right-4 top-4 text-xs font-black uppercase tracking-widest text-cantera-600">MXN</span>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Estado resultante</label>
                                <select name="estado_condicion" required class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-4 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400">
                                    @foreach ($estadosCondicion as $estadoCondicion)
                                        <option value="{{ $estadoCondicion }}" @selected(old('estado_condicion', 'Excelente') === $estadoCondicion)>
                                            {{ \App\Models\Activo::etiquetaEstadoCondicion($estadoCondicion) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Observaciones</label>
                            <textarea
                                name="observaciones"
                                rows="4"
                                class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-4 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                                placeholder="Describe el trabajo realizado, piezas reemplazadas o cualquier observación relevante."
                            >{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="rounded-2xl border border-cultura-100 bg-cultura-50 p-4">
                            <p class="text-xs font-black uppercase tracking-widest text-cultura-700">Finalizar y liberar</p>
                            <p class="mt-2 text-sm text-cultura-900">Al guardar, el sistema registra la nota del mantenimiento, reinicia el contador de horas de uso y vuelve a poner el instrumento como <span class="font-black">Disponible</span>.</p>
                        </div>

                        <label class="flex items-center gap-3 rounded-2xl border border-cantera-200 bg-hueso-50 p-4">
                            <input type="checkbox" name="es_preventivo" value="1" class="rounded border-cantera-300 text-cultura-600 focus:ring-ocre-400" @checked(old('es_preventivo'))>
                            <span class="text-sm text-anil-700">Mantenimiento preventivo por temporada o alta demanda.</span>
                        </label>

                        <button type="submit" class="btn-primary-wide">
                            Finalizar mantenimiento
                        </button>
                    </form>
                </section>

                <section class="space-y-8">
                    <div class="module-card">
                        <div class="flex items-center justify-between gap-3 border-b border-cantera-100 pb-4">
                            <h3 class="text-2xl font-black uppercase tracking-tight text-anil-900">En reparación</h3>
                            <span class="rounded-full bg-oxido-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-oxido-700">
                                {{ $activosEnMantenimiento->count() }} activos
                            </span>
                        </div>

                        <div class="mt-6 space-y-4">
                            @forelse ($activosEnMantenimiento as $item)
                                @php
                                    $ultimoServicio = $item->mantenimientos->sortByDesc('fecha_servicio')->first();
                                    $ultimoIncidente = $item->detallesPrestamo->sortByDesc('fecha_devolucion_real')->first();
                                @endphp
                                <div class="rounded-2xl border border-oxido-200 bg-oxido-50 p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-lg font-black text-oxido-800">{{ $item->nombre }}</p>
                                            <p class="mt-1 text-sm font-bold text-oxido-700">Estado: {{ $item->estado_actual }}</p>
                                            <p class="mt-1 text-xs font-bold uppercase tracking-widest text-oxido-600">Condición: {{ $item->estadoCondicionLegible() }}</p>
                                        </div>
                                        <span class="rounded-full bg-hueso-50 px-3 py-1 text-xs font-black uppercase tracking-widest text-oxido-700 shadow-sm">
                                            {{ number_format($item->horas_uso, 2) }} h
                                        </span>
                                    </div>

                                    @if ($ultimoIncidente)
                                        <div class="mt-4 rounded-xl border border-oxido-100 bg-hueso-50 p-4">
                                            <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Motivo de reparación</p>
                                            <p class="mt-2 text-sm font-bold text-anil-900">{{ $ultimoIncidente->prestamo?->condiciones_devolucion ?: 'Sin condiciones de retorno registradas' }}</p>
                                            <div class="mt-3 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                                                <div>
                                                    <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Contexto</p>
                                                    <p class="mt-1 text-anil-700">{{ $ultimoIncidente->contexto_incidente ?: 'Sin contexto registrado' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Entorno</p>
                                                    <p class="mt-1 text-anil-700">{{ $ultimoIncidente->entorno_uso ?: 'Sin entorno registrado' }}</p>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Accesorios de protección</p>
                                                    <p class="mt-1 text-anil-700">{{ $ultimoIncidente->accesorios_proteccion ?: 'Sin accesorios registrados' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($ultimoServicio)
                                        <div class="mt-4 rounded-xl border border-oxido-100 bg-hueso-50 p-4">
                                            <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Ultima nota registrada</p>
                                            <p class="mt-2 text-sm font-bold text-anil-900">{{ $ultimoServicio->tipo }}</p>
                                            <p class="mt-1 text-xs text-cantera-600">Costo: ${{ number_format((float) $ultimoServicio->costo_servicio, 2) }} MXN</p>
                                            @if ($ultimoServicio->observaciones)
                                                <p class="mt-1 text-sm text-cantera-700">{{ $ultimoServicio->observaciones }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-2xl border-2 border-dashed border-cantera-200 bg-hueso-50 py-10 text-center">
                                    <p class="font-bold text-cantera-600">No hay equipos en servicio técnico en este momento.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="flex items-center justify-between gap-3 border-b border-cantera-100 pb-4">
                            <h3 class="text-2xl font-black uppercase tracking-tight text-anil-900">Por Atender</h3>
                            <span class="rounded-full bg-ocre-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-ocre-700">
                                {{ $activosPorAtender->count() }} activos
                            </span>
                        </div>

                        <div class="mt-6 space-y-4">
                            @forelse ($activosPorAtender as $item)
                                <div class="rounded-2xl border border-ocre-200 bg-ocre-50 p-5">
                                    <p class="text-lg font-black text-ocre-800">{{ $item->nombre }}</p>
                                    <p class="mt-1 text-sm text-ocre-700">
                                        Horas acumuladas: <span class="font-black">{{ number_format($item->horas_uso, 2) }}</span>
                                        de
                                        <span class="font-black">{{ number_format($item->limite_mantenimiento, 2) }}</span>
                                    </p>
                                </div>
                            @empty
                                <div class="rounded-2xl border-2 border-dashed border-cantera-200 bg-hueso-50 py-10 text-center">
                                    <p class="font-bold text-cantera-600">No hay instrumentos pendientes por horas acumuladas.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <section class="module-card mt-8">
                <h3 class="border-b border-cantera-100 pb-4 text-2xl font-black uppercase tracking-tight text-anil-900">
                    Historial Reciente de Mantenimiento
                </h3>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b-2 border-cantera-100 text-xs uppercase tracking-widest text-cantera-500">
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
                                    <td class="py-4 text-sm text-cantera-700">{{ $registro->fecha_servicio->format('d/m/Y H:i') }}</td>
                                    <td class="py-4 font-bold text-anil-900">{{ $registro->activo?->nombre ?? 'Instrumento eliminado' }}</td>
                                    <td class="py-4 text-sm font-semibold text-anil-800">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span>{{ $registro->tipo }}</span>
                                            @if ($registro->es_preventivo)
                                                <span class="rounded-full bg-cantera-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-cantera-700">Preventivo</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 text-sm text-cantera-700">${{ number_format((float) $registro->costo_servicio, 2) }} MXN</td>
                                    <td class="py-4 text-sm text-cantera-700">{{ $registro->observaciones ?: 'Sin observaciones' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-cantera-500">Todavía no hay mantenimientos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="module-card mt-8">
                <h3 class="border-b border-cantera-100 pb-4 text-2xl font-black uppercase tracking-tight text-anil-900">
                    Informe de baja y adquisición
                </h3>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b-2 border-cantera-100 text-xs uppercase tracking-widest text-cantera-500">
                                <th class="pb-4">Modelo / Referencia</th>
                                <th class="pb-4">Categoría</th>
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
                                    <td class="py-4 font-bold text-anil-900">{{ $datos['referencia_modelo'] ?? ($informe->activo?->modelo ?: $informe->activo?->nombre ?? 'Sin referencia') }}</td>
                                    <td class="py-4 text-sm text-cantera-700">{{ $informe->activo?->categoria ?? 'Sin categoría' }}</td>
                                    <td class="py-4 text-sm text-cantera-700">
                                        <p class="font-black text-anil-900">${{ number_format($costoTotal, 2) }} MXN</p>
                                        <p class="text-xs text-cantera-600">Preventivo: ${{ number_format((float) ($datos['costo_mantenimientos_preventivos'] ?? 0), 2) }} MXN · {{ $preventivos }} servicios</p>
                                    </td>
                                    <td class="py-4 text-sm font-black text-anil-900">{{ number_format($porcentajeCosto, 1) }}%</td>
                                    <td class="py-4 text-sm text-cantera-700">{{ $datos['fallas'] ?? 0 }} en {{ $datos['periodo_dias'] ?? 365 }} días</td>
                                    <td class="py-4 text-sm text-anil-700">{{ $informe->descripcion }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-cantera-500">No hay modelos que ameriten reemplazo con los criterios actuales.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
