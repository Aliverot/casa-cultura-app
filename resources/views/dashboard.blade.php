<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Control - Casa de la Cultura') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-module-nav />

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div class="bg-white p-6 rounded-3xl shadow-lg border-b-4 border-blue-500 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Total activos</p>
                    <p class="text-5xl font-black text-gray-900 mt-3">{{ $stats['total'] }}</p>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-lg border-b-4 border-green-500 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Disponibles</p>
                    <p class="text-5xl font-black text-gray-900 mt-3">{{ $stats['disponibles'] }}</p>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-lg border-b-4 border-yellow-500 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Prestados / no disponibles</p>
                    <p class="text-5xl font-black text-gray-900 mt-3">{{ $stats['prestados'] }}</p>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-lg border-b-4 border-red-500 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Mantenimiento</p>
                    <p class="text-5xl font-black text-gray-900 mt-3">{{ $stats['mantenimiento'] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-[1.1fr_0.9fr] gap-8">
                <section class="bg-white overflow-hidden shadow-xl rounded-3xl p-8 border border-gray-200">
                    <h3 class="text-2xl font-black mb-6 text-gray-900 border-b border-gray-100 pb-4 uppercase tracking-tighter">Acciones operativas</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('activos.index') }}" class="flex flex-col items-center p-6 bg-blue-50 rounded-2xl border border-blue-100 hover:bg-blue-100 hover:scale-105 hover:shadow-md transition transform text-center">
                            <span class="text-4xl mb-3">Catalogo</span>
                            <p class="font-black text-blue-900 uppercase tracking-widest text-sm">Inventario</p>
                            <p class="text-xs text-blue-700 mt-1">Consultar y prestar instrumentos</p>
                        </a>

                        <a href="{{ route('prestamos.create') }}" class="flex flex-col items-center p-6 bg-green-50 rounded-2xl border border-green-100 hover:bg-green-100 hover:scale-105 hover:shadow-md transition transform text-center">
                            <span class="text-4xl mb-3">Prestamo</span>
                            <p class="font-black text-green-900 uppercase tracking-widest text-sm">Nuevo prestamo</p>
                            <p class="text-xs text-green-700 mt-1">Registrar salida con fecha y hora automaticas</p>
                        </a>

                        <a href="{{ route('prestamos.activos') }}" class="flex flex-col items-center p-6 bg-purple-50 rounded-2xl border border-purple-100 hover:bg-purple-100 hover:scale-105 hover:shadow-md transition transform text-center">
                            <span class="text-4xl mb-3">Devolucion</span>
                            <p class="font-black text-purple-900 uppercase tracking-widest text-sm">Devoluciones</p>
                            <p class="text-xs text-purple-700 mt-1">Recibir equipo y registrar pagos</p>
                        </a>

                        <a href="{{ route('mantenimientos.index') }}" class="flex flex-col items-center p-6 bg-amber-50 rounded-2xl border border-amber-100 hover:bg-amber-100 hover:scale-105 hover:shadow-md transition transform text-center">
                            <span class="text-4xl mb-3">Servicio</span>
                            <p class="font-black text-amber-900 uppercase tracking-widest text-sm">Mantenimiento</p>
                            <p class="text-xs text-amber-700 mt-1">Atender reparaciones y liberar equipo</p>
                        </a>
                    </div>
                </section>

                <section class="bg-white overflow-hidden shadow-xl rounded-3xl p-8 border border-gray-200">
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-4">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter">Panel de metricas</h3>
                            <p class="text-sm text-gray-500 mt-1">Instrumentos con mayor demanda por numero de prestamos registrados.</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-5">
                        @forelse ($metricasDemanda as $item)
                            @php
                                $anchoBarra = max(12, (int) round(($item->total_prestamos / $maxPrestamos) * 100));
                            @endphp
                            <div>
                                <div class="flex items-end justify-between gap-4 mb-2">
                                    <div>
                                        <p class="font-black text-gray-900">{{ $item->nombre }}</p>
                                        <p class="text-xs font-bold uppercase tracking-widest text-gray-500">{{ $item->categoria }}</p>
                                    </div>
                                    <p class="text-sm font-black text-cultura-700">{{ $item->total_prestamos }} prestamos</p>
                                </div>
                                <div class="h-4 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-cultura-600" style="width: {{ $anchoBarra }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 py-12 text-center">
                                <p class="font-bold text-gray-500">Todavia no hay datos suficientes para mostrar metricas.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
