<x-app-layout>
    <div class="py-12 bg-hueso-100 min-h-screen">
        <div class="module-page-shell">
            <x-module-nav current="historial" />

            @if ($errors->any())
                <div class="mb-8 rounded-r border-l-4 border-oxido-500 bg-oxido-50 p-4 text-oxido-800 shadow-sm">
                    <p class="mb-2 font-black uppercase tracking-widest">No se pudo generar la consulta</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="module-card mb-8">
                <div class="flex flex-col gap-4 border-b border-cantera-100 pb-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-2xl font-black uppercase tracking-tight text-anil-900">Filtros del histórico</h3>
                    </div>
                    <a href="{{ route('prestamos.historial') }}" class="btn-cancel">
                        Limpiar
                    </a>
                </div>

                <form method="GET" action="{{ route('prestamos.historial') }}" class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-[0.85fr_0.85fr_1.4fr_1.2fr_auto]">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Desde</label>
                        <input
                            type="date"
                            name="desde"
                            value="{{ $filtros['desde'] }}"
                            class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-3 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Hasta</label>
                        <input
                            type="date"
                            name="hasta"
                            value="{{ $filtros['hasta'] }}"
                            class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-3 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Instrumento</label>
                        <select name="id_activo" class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-3 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400">
                            <option value="">Todos los instrumentos</option>
                            @foreach ($activosFiltro as $activoFiltro)
                                <option value="{{ $activoFiltro->id_activo }}" @selected((string) $filtros['id_activo'] === (string) $activoFiltro->id_activo)>
                                    {{ $activoFiltro->nombre }} - {{ $activoFiltro->codigo_qr }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Solicitante</label>
                        <input
                            type="text"
                            name="solicitante"
                            value="{{ $filtros['solicitante'] }}"
                            placeholder="Nombre del solicitante"
                            class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-3 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                        >
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row xl:flex-col xl:justify-end">
                        <button type="submit" class="btn-primary">
                            Filtrar
                        </button>
                        <button
                            type="submit"
                            formaction="{{ route('prestamos.historial.csv') }}"
                            class="btn-soft min-h-11 px-5 py-3 text-sm"
                        >
                            Generar CSV
                        </button>
                    </div>
                </form>
            </section>

            <div class="module-card">
                <h3 class="text-2xl font-black text-anil-900 mb-6">Registro histórico de préstamos</h3>

                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-2xl border border-anil-100 bg-anil-50 p-4">
                        <p class="text-xs font-black uppercase tracking-widest text-anil-700">Registros</p>
                        <p class="mt-2 text-3xl font-black text-anil-900">{{ $resumen['registros'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-cantera-100 bg-cantera-50 p-4">
                        <p class="text-xs font-black uppercase tracking-widest text-cantera-700">Horas</p>
                        <p class="mt-2 text-3xl font-black text-cantera-900">{{ number_format($resumen['horas'], 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-oxido-100 bg-oxido-50 p-4">
                        <p class="text-xs font-black uppercase tracking-widest text-oxido-700">Incidencias</p>
                        <p class="mt-2 text-3xl font-black text-oxido-900">{{ $resumen['incidencias'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-ocre-100 bg-ocre-50 p-4">
                        <p class="text-xs font-black uppercase tracking-widest text-ocre-700">Fuera de plazo</p>
                        <p class="mt-2 text-3xl font-black text-ocre-900">{{ $resumen['atrasos'] }}</p>
                    </div>
                </div>

                <div class="mb-6 rounded-2xl border border-cultura-100 bg-cultura-50 p-4 text-sm text-cultura-900">
                    <p><span class="font-black">Préstamo registrado:</span> momento en que el instrumento salió.</p>
                    <p class="mt-1"><span class="font-black">Fecha límite de devolución:</span> cuando debía regresar.</p>
                    <p class="mt-1"><span class="font-black">Devolución recibida:</span> cuando realmente fue entregado.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-hueso-50 border-b-2 border-cantera-200 text-cantera-600 text-xs uppercase tracking-widest">
                                <th class="p-4 rounded-tl-lg">Fechas</th>
                                <th class="p-4">Instrumento</th>
                                <th class="p-4">Solicitante</th>
                                <th class="p-4">Resultado</th>
                                <th class="p-4 rounded-tr-lg">Tiempo de uso</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($historial as $log)
                                @php
                                    $prevista = $log->prestamo->fecha_devolucion_prevista;
                                    $salida = $log->prestamo->fecha_salida;
                                    $real = $log->fecha_devolucion_real;
                                    $horasPrestamo = round($salida->diffInSeconds($real) / 3600, 2);
                                    $entregaATiempo = $real->lessThanOrEqualTo($prevista);
                                    $estadoRetorno = in_array($log->estado_retorno, ['Danado', 'Dañado'], true)
                                        ? 'Dañado'
                                        : ($log->estado_retorno === 'Perdida total' ? 'Pérdida total' : $log->estado_retorno);
                                    $esIncidenciaMayor = in_array($log->estado_retorno, ['Danado', 'Dañado', 'Extraviado', 'Perdida total'], true);
                                @endphp

                                <tr class="hover:bg-hueso-50 transition">
                                    <td class="p-4 text-sm text-cantera-700 whitespace-nowrap">
                                        <p><span class="font-bold text-anil-900">Préstamo registrado:</span> {{ $salida->format('d/m/Y H:i') }}</p>
                                        <p class="mt-1"><span class="font-bold text-anil-900">Fecha límite:</span> {{ $prevista->format('d/m/Y H:i') }}</p>
                                        <p class="mt-1"><span class="font-bold text-anil-900">Devolución recibida:</span> {{ $real->format('d/m/Y H:i') }}</p>
                                    </td>

                                    <td class="p-4">
                                        <p class="font-black text-anil-900">{{ $log->activo->nombre }}</p>
                                        <p class="text-xs text-cantera-500 font-mono">{{ $log->activo->codigo_qr }}</p>
                                    </td>

                                    <td class="p-4">
                                        <p class="font-bold text-anil-700">{{ $log->prestamo->nombre_solicitante }}</p>
                                        <p class="text-xs text-cantera-600">Teléfono: {{ $log->prestamo->contacto_solicitante }}</p>
                                    </td>

                                    <td class="p-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-black uppercase
                                            @if ($esIncidenciaMayor)
                                                bg-oxido-100 text-oxido-700
                                            @elseif ($entregaATiempo)
                                                bg-cantera-100 text-cantera-700
                                            @else
                                                bg-ocre-100 text-ocre-700
                                            @endif
                                        ">
                                            {{ $estadoRetorno }}
                                        </span>
                                        <div class="mt-3 rounded-xl border border-cantera-100 bg-hueso-50 p-3">
                                            <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Condiciones de retorno</p>
                                            <p class="mt-1 text-sm text-anil-700">{{ $log->prestamo->condiciones_devolucion ?: 'Sin condiciones registradas' }}</p>
                                            @if ($esIncidenciaMayor)
                                                <div class="mt-3 space-y-2 text-xs text-cantera-700">
                                                    <p><span class="font-black uppercase tracking-widest text-cantera-500">Contexto:</span> {{ $log->contexto_incidente ?: 'Sin contexto registrado' }}</p>
                                                    <p><span class="font-black uppercase tracking-widest text-cantera-500">Entorno:</span> {{ $log->entorno_uso ?: 'Sin entorno registrado' }}</p>
                                                    <p><span class="font-black uppercase tracking-widest text-cantera-500">Protección:</span> {{ $log->accesorios_proteccion ?: 'Sin accesorios registrados' }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="p-4">
                                        <p class="font-black text-anil-900">{{ number_format($horasPrestamo, 2) }} horas</p>
                                        <p class="text-xs {{ $entregaATiempo ? 'text-cantera-600' : 'text-ocre-600' }}">
                                            {{ $entregaATiempo ? 'Entrega dentro del plazo' : 'Entrega fuera del plazo' }}
                                        </p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-cantera-500 font-medium text-lg">
                                        Aún no hay registros en la bitácora.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
