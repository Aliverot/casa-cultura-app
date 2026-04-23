<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bitácora General (Historial de Movimientos)') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white p-4 rounded-lg flex flex-wrap justify-center gap-4 mb-8 shadow-sm border border-gray-200">
                <a href="{{ route('catalogo') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Catálogo</a>
                <a href="{{ route('activos.create') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Instrumentos Nuevos</a>
                <a href="{{ route('prestamos.activos') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Devoluciones / Multas</a>
                <a href="{{ route('mantenimiento.index') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Mantenimiento</a>
                <a href="{{ route('prestamos.historial') }}" class="bg-blue-600 text-white px-6 py-2 rounded-md shadow-md font-bold">Historial (Log)</a>
            </div>

            <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-200">
                <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center">
                    <span class="mr-3">📂</span> Registro Histórico de Préstamos
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b-2 border-gray-200 text-gray-500 text-xs uppercase tracking-widest">
                                <th class="p-4 rounded-tl-lg">Fecha Salida</th>
                                <th class="p-4">Instrumento</th>
                                <th class="p-4">Solicitante</th>
                                <th class="p-4">Puntualidad</th>
                                <th class="p-4">Condición de Retorno</th>
                                <th class="p-4 rounded-tr-lg">Multa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($historial as $log)
                                @php
                                    // Lógica para saber si entregó tarde
                                    $prevista = \Carbon\Carbon::parse($log->prestamo->fecha_devolucion_prevista);
                                    $real = \Carbon\Carbon::parse($log->fecha_devolucion_real);
                                    $esAtrasado = $real->gt($prevista);
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 text-sm text-gray-600 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($log->prestamo->fecha_salida)->format('d/m/Y') }}
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
                                        @if($esAtrasado)
                                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-md text-xs font-black uppercase">Con Atraso</span>
                                            <p class="text-xs text-gray-400 mt-1">Debió llegar: {{ $prevista->format('d/m/Y') }}</p>
                                        @else
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-md text-xs font-black uppercase">A Tiempo</span>
                                        @endif
                                    </td>
                                    
                                    <td class="p-4">
                                        @if($log->estado_retorno == 'Dañado')
                                            <span class="text-red-600 font-bold text-sm">⚠️ Dañado</span>
                                            <p class="text-xs text-gray-500 italic mt-1 max-w-xs">{{ $log->prestamo->condiciones_devolucion }}</p>
                                        @else
                                            <span class="text-green-600 font-bold text-sm">✔️ Buen Estado</span>
                                        @endif
                                    </td>

                                    <td class="p-4">
                                        @if($log->prestamo->costo_reparacion > 0)
                                            <p class="font-black text-red-600">${{ number_format($log->prestamo->costo_reparacion, 2) }}</p>
                                            <p class="text-xs font-bold {{ $log->prestamo->estado_pago == 'Pagado' ? 'text-green-500' : 'text-orange-500' }}">
                                                {{ $log->prestamo->estado_pago }}
                                            </p>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
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