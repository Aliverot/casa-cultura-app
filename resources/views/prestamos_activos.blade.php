<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Centro de Devoluciones y Multas') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white p-4 rounded-lg flex flex-wrap justify-center gap-4 mb-8 shadow-sm border border-gray-200">
                <a href="{{ route('catalogo') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Catálogo</a>
                <a href="{{ route('activos.create') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Instrumentos Nuevos</a>
                <a href="{{ route('prestamos.activos') }}" class="bg-blue-600 text-white px-6 py-2 rounded-md shadow-md font-bold">Devoluciones / Multas</a>
                <a href="{{ route('mantenimiento.index') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Mantenimiento</a>
                <a href="{{ route('prestamos.historial') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-blue-600 hover:bg-gray-50 rounded-md transition">Historial (Log)</a>
            </div>

            <div class="bg-white rounded-3xl shadow-xl p-8 mb-10 border border-gray-200">
                <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center">
                    <span class="mr-3">🎸</span> Préstamos en Curso (Por Devolver)
                </h3>

                <div class="grid grid-cols-1 gap-6">
                    @forelse($prestamosActivos as $item)
                        <div class="border-2 border-gray-100 rounded-2xl p-6 hover:border-blue-200 transition bg-gray-50">
                            <div class="flex flex-col md:flex-row justify-between">
                                <div class="space-y-2">
                                    <h4 class="text-xl font-black text-blue-700">{{ $item->activo->nombre }}</h4>
                                    <p class="text-sm text-gray-600 font-bold uppercase tracking-tighter">Responsable: <span class="text-gray-900">{{ $item->prestamo->nombre_solicitante }}</span></p>
                                    <p class="text-xs text-gray-500">Contacto: {{ $item->prestamo->contacto_solicitante }}</p>
                                    <div class="bg-white p-3 rounded-lg border border-gray-200 mt-3">
                                        <p class="text-xs font-black text-gray-400 uppercase">Se entregó así:</p>
                                        <p class="text-sm italic text-gray-700">"{{ $item->prestamo->condiciones_entrega }}"</p>
                                    </div>
                                </div>

                                <div class="mt-6 md:mt-0 md:w-1/3">
                                    <form action="{{ route('prestamos.devolver', $item->id_detalle) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase">Condiciones de retorno:</label>
                                            <textarea name="condiciones_devolucion" required class="w-full rounded-lg border-gray-300 text-sm p-2 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500" rows="2" placeholder="¿Cómo regresa el equipo?"></textarea>
                                        </div>
                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase">Costo Reparación (Opcional):</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-2 text-gray-400">$</span>
                                                <input type="number" name="costo_reparacion" step="0.01" class="w-full rounded-lg border-gray-300 pl-7 p-2 text-sm bg-white text-gray-900 focus:ring-2 focus:ring-blue-500" placeholder="0.00">
                                            </div>
                                        </div>
                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-black py-3 rounded-xl shadow-md uppercase text-xs tracking-widest">
                                            Procesar Devolución
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-400 py-10 font-medium">No hay instrumentos fuera de la institución actualmente.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-200">
                <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center">
                    <span class="mr-3">💰</span> Multas y Pagos Pendientes
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b-2 border-gray-100 text-gray-400 text-xs uppercase tracking-widest">
                                <th class="pb-4">Instrumento</th>
                                <th class="pb-4">Responsable</th>
                                <th class="pb-4">Daño Reportado</th>
                                <th class="pb-4">Monto</th>
                                <th class="pb-4">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($multasPendientes as $multa)
                                <tr>
                                    <td class="py-4 font-bold text-gray-900">{{ $multa->activo->nombre }}</td>
                                    <td class="py-4 text-sm text-gray-600">{{ $multa->prestamo->nombre_solicitante }}</td>
                                    <td class="py-4 text-sm italic text-gray-500 max-w-xs">{{ $multa->prestamo->condiciones_devolucion }}</td>
                                    <td class="py-4">
                                        <span class="text-red-600 font-black text-lg">${{ number_format($multa->prestamo->costo_reparacion, 2) }}</span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <form action="{{ route('prestamos.liquidar', $multa->prestamo->id_prestamo) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-blue-100 text-blue-700 hover:bg-blue-600 hover:text-white px-4 py-2 rounded-lg font-black text-xs uppercase transition">
                                                Marcar Pagado
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-gray-400 font-medium">No hay cobros pendientes de reparaciones.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>