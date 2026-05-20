<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bitacora General de Prestamos') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="module-page-shell">
            <x-module-nav current="historial" />

            <div class="module-card">
                <h3 class="text-2xl font-black text-gray-900 mb-6">Registro historico de prestamos</h3>

                <div class="mb-6 rounded-2xl border border-cultura-100 bg-cultura-50 p-4 text-sm text-cultura-900">
                    <p><span class="font-black">Prestamo registrado:</span> momento en que el instrumento salio.</p>
                    <p class="mt-1"><span class="font-black">Fecha limite de devolucion:</span> cuando debia regresar.</p>
                    <p class="mt-1"><span class="font-black">Devolucion recibida:</span> cuando realmente fue entregado.</p>
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
                                    $estadoRetorno = in_array($log->estado_retorno, ['Danado', 'Dañado', 'DaÃ±ado'], true)
                                        ? 'Da&ntilde;ado'
                                        : ($log->estado_retorno === 'Perdida total' ? 'P&eacute;rdida total' : $log->estado_retorno);
                                    $esIncidenciaMayor = in_array($log->estado_retorno, ['Danado', 'Dañado', 'DaÃ±ado', 'Extraviado', 'Perdida total'], true);
                                @endphp

                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 text-sm text-gray-600 whitespace-nowrap">
                                        <p><span class="font-bold text-gray-900">Prestamo registrado:</span> {{ $salida->format('d/m/Y H:i') }}</p>
                                        <p class="mt-1"><span class="font-bold text-gray-900">Fecha limite:</span> {{ $prevista->format('d/m/Y H:i') }}</p>
                                        <p class="mt-1"><span class="font-bold text-gray-900">Devolucion recibida:</span> {{ $real->format('d/m/Y H:i') }}</p>
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
                                            {!! $estadoRetorno !!}
                                        </span>
                                        <div class="mt-3 rounded-xl border border-gray-100 bg-white p-3">
                                            <p class="text-xs font-black uppercase tracking-widest text-gray-400">Condiciones de retorno</p>
                                            <p class="mt-1 text-sm text-gray-700">{{ $log->prestamo->condiciones_devolucion ?: 'Sin condiciones registradas' }}</p>
                                            @if ($esIncidenciaMayor)
                                                <div class="mt-3 space-y-2 text-xs text-gray-600">
                                                    <p><span class="font-black uppercase tracking-widest text-gray-400">Contexto:</span> {{ $log->contexto_incidente ?: 'Sin contexto registrado' }}</p>
                                                    <p><span class="font-black uppercase tracking-widest text-gray-400">Entorno:</span> {{ $log->entorno_uso ?: 'Sin entorno registrado' }}</p>
                                                    <p><span class="font-black uppercase tracking-widest text-gray-400">Proteccion:</span> {{ $log->accesorios_proteccion ?: 'Sin accesorios registrados' }}</p>
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
                                            <p class="font-black text-red-600">${{ number_format((float) $log->prestamo->costo_reparacion, 2) }}</p>
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
                                        Aun no hay registros en la bitacora.
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
