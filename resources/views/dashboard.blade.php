<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Control - Casa de la Cultura') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="module-page-shell">
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

            @if ($alertasOperativas->isNotEmpty())
                <section class="module-card mb-8">
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-4">
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter">Alertas operativas</h3>
                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-red-700">
                            {{ $alertasOperativas->count() }} pendientes
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        @foreach ($alertasOperativas as $alerta)
                            @php
                                [$alertaBorde, $alertaFondo, $alertaTexto, $alertaCaja] = match ($alerta->tipo) {
                                    'Fragilidad/Mal Uso' => ['border-red-100', 'bg-red-50', 'text-red-700', 'border-red-200'],
                                    'Preparacion de Temporada' => ['border-blue-100', 'bg-blue-50', 'text-blue-700', 'border-blue-200'],
                                    'Baja y Adquisicion' => ['border-amber-100', 'bg-amber-50', 'text-amber-700', 'border-amber-200'],
                                    default => ['border-slate-100', 'bg-slate-50', 'text-slate-700', 'border-slate-200'],
                                };
                            @endphp
                            <div class="rounded-2xl border {{ $alertaBorde }} {{ $alertaFondo }} p-5">
                                @if ($alerta->activo)
                                    <div class="mb-4 rounded-xl border {{ $alertaCaja }} bg-white px-4 py-3">
                                        <p class="text-xs font-black uppercase tracking-widest text-gray-500">Instrumento afectado</p>
                                        <p class="mt-1 text-xl font-black {{ $alertaTexto }}">{{ $alerta->activo->nombre }}</p>
                                    </div>
                                @endif
                                <p class="text-xs font-black uppercase tracking-widest {{ $alertaTexto }}">{{ $alerta->tipo }}</p>
                                <p class="mt-2 text-lg font-black text-gray-900">{{ $alerta->titulo }}</p>
                                <p class="mt-1 text-sm text-gray-700">{{ $alerta->descripcion }}</p>
                                @if ($alerta->tipo === 'Baja y Adquisicion' && is_array($alerta->datos))
                                    <div class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                                        <div class="rounded-xl bg-white p-3">
                                            <p class="text-xs font-black uppercase tracking-widest text-gray-400">Valor original</p>
                                            <p class="mt-1 font-black text-gray-900">${{ number_format((float) ($alerta->datos['valor_original'] ?? 0), 2) }}</p>
                                        </div>
                                        <div class="rounded-xl bg-white p-3">
                                            <p class="text-xs font-black uppercase tracking-widest text-gray-400">Reparaciones</p>
                                            <p class="mt-1 font-black text-gray-900">${{ number_format((float) ($alerta->datos['costo_reparaciones'] ?? 0), 2) }}</p>
                                        </div>
                                        <div class="rounded-xl bg-white p-3">
                                            <p class="text-xs font-black uppercase tracking-widest text-gray-400">Limite</p>
                                            <p class="mt-1 font-black text-gray-900">{{ $alerta->datos['porcentaje_limite'] ?? 60 }}%</p>
                                        </div>
                                    </div>
                                @elseif ($alerta->tipo === 'Preparacion de Temporada' && is_array($alerta->datos))
                                    <div class="mt-4 rounded-xl bg-white p-3 text-sm">
                                        <p class="text-xs font-black uppercase tracking-widest text-gray-400">Base de alerta</p>
                                        @if (! empty($alerta->datos['temporada_base']))
                                            <p class="mt-1 font-bold text-gray-900">{{ $alerta->datos['temporada_base']['nombre'] }}: {{ $alerta->datos['temporada_base']['rango'] }}</p>
                                        @else
                                            <p class="mt-1 font-bold text-gray-900">Incremento reciente: {{ $alerta->datos['incremento_porcentaje'] ?? 0 }}%</p>
                                        @endif
                                    </div>
                                @elseif ($alerta->tipo === 'Fragilidad/Mal Uso' && is_array($alerta->datos))
                                    <div class="mt-4 rounded-xl bg-white p-3 text-sm">
                                        <p class="text-xs font-black uppercase tracking-widest text-gray-400">Danos recientes</p>
                                        <p class="mt-1 font-bold text-gray-900">{{ $alerta->datos['danios_recientes'] ?? 0 }} en {{ $alerta->datos['periodo_dias'] ?? 90 }} dias</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-[1.1fr_0.9fr] gap-8">
                <section class="module-card overflow-hidden">
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

                <section class="module-card overflow-hidden">
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
