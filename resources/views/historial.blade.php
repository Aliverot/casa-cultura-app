<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bitácora general de préstamos') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="module-page-shell">
            <x-module-nav current="historial" />

            @if ($errors->any())
                <div class="mb-8 rounded-r border-l-4 border-red-500 bg-red-100 p-4 text-red-800 shadow-sm">
                    <p class="mb-2 font-black uppercase tracking-widest">No se pudo generar la consulta</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="module-card mb-8">
                <div class="flex flex-col gap-4 border-b border-gray-100 pb-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-2xl font-black uppercase tracking-tight text-gray-900">Filtros del histórico</h3>
                    </div>
                    <a href="{{ route('prestamos.historial') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                        Limpiar
                    </a>
                </div>

                <form method="GET" action="{{ route('prestamos.historial') }}" class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-[0.85fr_0.85fr_1.4fr_1.2fr_auto]">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Desde</label>
                        <input
                            type="date"
                            name="desde"
                            value="{{ $filtros['desde'] }}"
                            class="w-full rounded-xl border-gray-300 bg-white p-3 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Hasta</label>
                        <input
                            type="date"
                            name="hasta"
                            value="{{ $filtros['hasta'] }}"
                            class="w-full rounded-xl border-gray-300 bg-white p-3 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Instrumento</label>
                        <select name="id_activo" class="w-full rounded-xl border-gray-300 bg-white p-3 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500">
                            <option value="">Todos los instrumentos</option>
                            @foreach ($activosFiltro as $activoFiltro)
                                <option value="{{ $activoFiltro->id_activo }}" @selected((string) $filtros['id_activo'] === (string) $activoFiltro->id_activo)>
                                    {{ $activoFiltro->nombre }} - {{ $activoFiltro->codigo_qr }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Solicitante</label>
                        <input
                            type="text"
                            name="solicitante"
                            value="{{ $filtros['solicitante'] }}"
                            placeholder="Nombre del solicitante"
                            class="w-full rounded-xl border-gray-300 bg-white p-3 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                        >
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row xl:flex-col xl:justify-end">
                        <button type="submit" class="rounded-xl bg-cultura-600 px-5 py-3 text-sm font-black uppercase tracking-widest text-white shadow transition hover:bg-cultura-700">
                            Filtrar
                        </button>
                        <button
                            type="submit"
                            formaction="{{ route('prestamos.historial.csv') }}"
                            class="rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-sm font-black uppercase tracking-widest text-green-800 transition hover:bg-green-100"
                        >
                            Generar CSV
                        </button>
                    </div>
                </form>
            </section>

            <div class="module-card">
                <h3 class="text-2xl font-black text-gray-900 mb-6">Registro histórico de préstamos</h3>

                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                        <p class="text-xs font-black uppercase tracking-widest text-blue-700">Registros</p>
                        <p class="mt-2 text-3xl font-black text-blue-950">{{ $resumen['registros'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-green-100 bg-green-50 p-4">
                        <p class="text-xs font-black uppercase tracking-widest text-green-700">Horas</p>
                        <p class="mt-2 text-3xl font-black text-green-950">{{ number_format($resumen['horas'], 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-red-100 bg-red-50 p-4">
                        <p class="text-xs font-black uppercase tracking-widest text-red-700">Cargos</p>
                        <p class="mt-2 text-3xl font-black text-red-950">${{ number_format($resumen['cargos'], 2) }} MXN</p>
                    </div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                        <p class="text-xs font-black uppercase tracking-widest text-amber-700">Pagos pendientes</p>
                        <p class="mt-2 text-3xl font-black text-amber-950">{{ $resumen['pendientes'] }}</p>
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
                            <tr class="bg-gray-50 border-b-2 border-gray-200 text-gray-500 text-xs uppercase tracking-widest">
                                <th class="p-4 rounded-tl-lg">Fechas</th>
                                <th class="p-4">Instrumento</th>
                                <th class="p-4">Solicitante</th>
                                <th class="p-4">Resultado</th>
                                <th class="p-4">Tiempo de uso</th>
                                <th class="p-4 rounded-tr-lg">Pago</th>
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

                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 text-sm text-gray-600 whitespace-nowrap">
                                        <p><span class="font-bold text-gray-900">Préstamo registrado:</span> {{ $salida->format('d/m/Y H:i') }}</p>
                                        <p class="mt-1"><span class="font-bold text-gray-900">Fecha límite:</span> {{ $prevista->format('d/m/Y H:i') }}</p>
                                        <p class="mt-1"><span class="font-bold text-gray-900">Devolución recibida:</span> {{ $real->format('d/m/Y H:i') }}</p>
                                    </td>

                                    <td class="p-4">
                                        <p class="font-black text-gray-900">{{ $log->activo->nombre }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $log->activo->codigo_qr }}</p>
                                    </td>

                                    <td class="p-4">
                                        <p class="font-bold text-gray-700">{{ $log->prestamo->nombre_solicitante }}</p>
                                        <p class="text-xs text-gray-500">{{ $log->prestamo->contacto_solicitante }}</p>
                                    </td>

                                    <td class="p-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-black uppercase
                                            @if ($esIncidenciaMayor)
                                                bg-red-100 text-red-700
                                            @elseif ($entregaATiempo)
                                                bg-green-100 text-green-700
                                            @else
                                                bg-yellow-100 text-yellow-700
                                            @endif
                                        ">
                                            {{ $estadoRetorno }}
                                        </span>
                                        <div class="mt-3 rounded-xl border border-gray-100 bg-white p-3">
                                            <p class="text-xs font-black uppercase tracking-widest text-gray-400">Condiciones de retorno</p>
                                            <p class="mt-1 text-sm text-gray-700">{{ $log->prestamo->condiciones_devolucion ?: 'Sin condiciones registradas' }}</p>
                                            @if ($esIncidenciaMayor)
                                                <div class="mt-3 space-y-2 text-xs text-gray-600">
                                                    <p><span class="font-black uppercase tracking-widest text-gray-400">Contexto:</span> {{ $log->contexto_incidente ?: 'Sin contexto registrado' }}</p>
                                                    <p><span class="font-black uppercase tracking-widest text-gray-400">Entorno:</span> {{ $log->entorno_uso ?: 'Sin entorno registrado' }}</p>
                                                    <p><span class="font-black uppercase tracking-widest text-gray-400">Protección:</span> {{ $log->accesorios_proteccion ?: 'Sin accesorios registrados' }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="p-4">
                                        <p class="font-black text-gray-900">{{ number_format($horasPrestamo, 2) }} horas</p>
                                        <p class="text-xs {{ $entregaATiempo ? 'text-green-600' : 'text-yellow-600' }}">
                                            {{ $entregaATiempo ? 'Entrega dentro del plazo' : 'Entrega fuera del plazo' }}
                                        </p>
                                    </td>

                                    <td class="p-4">
                                        @if ((float) $log->prestamo->costo_reparacion > 0)
                                            <p class="font-black text-red-600">${{ number_format((float) $log->prestamo->costo_reparacion, 2) }} MXN</p>
                                            <p class="text-xs font-bold {{ $log->prestamo->estado_pago === 'Pagado' ? 'text-green-500' : 'text-orange-500' }}">
                                                {{ $log->prestamo->estado_pago }}
                                            </p>
                                        @else
                                            <span class="text-gray-400 text-sm">Sin cargo</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-10 text-center text-gray-400 font-medium text-lg">
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
