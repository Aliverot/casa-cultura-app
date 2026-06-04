<x-app-layout>
    <div class="py-12 bg-hueso-100 min-h-screen">
        <div class="module-page-shell">
            <x-module-nav />

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div class="bg-hueso-50 p-6 rounded-xl shadow-lg shadow-cantera-900/10 border-b-4 border-cantera-600 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-cantera-700 uppercase tracking-widest">Total activos</p>
                    <p class="text-5xl font-black text-anil-900 mt-3">{{ $stats['total'] }}</p>
                </div>

                <div class="bg-hueso-50 p-6 rounded-xl shadow-lg shadow-cantera-900/10 border-b-4 border-cantera-500 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-cantera-700 uppercase tracking-widest">Disponibles</p>
                    <p class="text-5xl font-black text-cantera-800 mt-3">{{ $stats['disponibles'] }}</p>
                </div>

                <div class="bg-hueso-50 p-6 rounded-xl shadow-lg shadow-cantera-900/10 border-b-4 border-ocre-400 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-ocre-700 uppercase tracking-widest">Prestados / no disponibles</p>
                    <p class="text-5xl font-black text-ocre-800 mt-3">{{ $stats['prestados'] }}</p>
                </div>

                <div class="bg-hueso-50 p-6 rounded-xl shadow-lg shadow-cantera-900/10 border-b-4 border-oxido-500 transform hover:-translate-y-1 transition duration-300">
                    <p class="text-xs font-black text-oxido-700 uppercase tracking-widest">Mantenimiento</p>
                    <p class="text-5xl font-black text-oxido-800 mt-3">{{ $stats['mantenimiento'] }}</p>
                </div>
            </div>

            @if ($alertasOperativas->isNotEmpty())
                @php
                    $totalAlertas = $alertasOperativas->count();
                    $alertasOcultas = max(0, $totalAlertas - 6);
                @endphp

                <section class="module-card mb-8" x-data="{ mostrarTodas: false }">
                    <div class="flex items-center justify-between gap-4 border-b border-cantera-100 pb-4">
                        <h3 class="text-2xl font-black text-anil-900 uppercase tracking-tighter">Alertas operativas</h3>

                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <span class="rounded-full bg-oxido-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-oxido-700">
                                {{ $totalAlertas }} pendientes
                            </span>

                            @if ($alertasOcultas > 0)
                                <button type="button" class="btn-soft" @click="mostrarTodas = !mostrarTodas">
                                    <span x-show="!mostrarTodas">Ver todas ({{ $alertasOcultas }} más)</span>
                                    <span x-show="mostrarTodas" x-cloak>Ver menos</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 pr-2" :class="mostrarTodas ? 'max-h-[38rem] overflow-y-auto' : ''">
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            @foreach ($alertasOperativas as $alerta)
                            @php
                                [$alertaBorde, $alertaFondo, $alertaTexto, $alertaCaja] = match ($alerta->tipo) {
                                    'Fragilidad/Mal Uso' => ['border-oxido-100', 'bg-oxido-50', 'text-oxido-700', 'border-oxido-200'],
                                    'Atencion a Dano' => ['border-oxido-100', 'bg-oxido-50', 'text-oxido-700', 'border-oxido-200'],
                                    'Preparacion de Temporada' => ['border-anil-100', 'bg-anil-50', 'text-anil-700', 'border-anil-200'],
                                    'Agenda Diaria de Prestamos' => ['border-anil-100', 'bg-anil-50', 'text-anil-700', 'border-anil-200'],
                                    'Incremento Historico de Prestamos' => ['border-cantera-100', 'bg-cantera-50', 'text-cantera-700', 'border-cantera-200'],
                                    'Baja y Adquisicion' => ['border-ocre-100', 'bg-ocre-50', 'text-ocre-700', 'border-ocre-200'],
                                    default => ['border-hueso-200', 'bg-hueso-50', 'text-anil-700', 'border-hueso-300'],
                                };

                                $alertaTipo = match ($alerta->tipo) {
                                    'Atencion a Dano' => 'Atención a daño',
                                    'Preparacion de Temporada' => 'Preparación de temporada',
                                    'Agenda Diaria de Prestamos' => 'Agenda diaria de préstamos',
                                    'Incremento Historico de Prestamos' => 'Incremento histórico de préstamos',
                                    'Baja y Adquisicion' => 'Baja y adquisición',
                                    default => $alerta->tipo,
                                };
                            @endphp
                            <div
                                @if ($loop->iteration > 6) x-show="mostrarTodas" x-cloak @endif
                                class="rounded-xl border {{ $alertaBorde }} {{ $alertaFondo }} p-5"
                            >
                                @if ($alerta->activo)
                                    <div class="mb-4 rounded-xl border {{ $alertaCaja }} bg-hueso-50 px-4 py-3">
                                        <p class="text-xs font-black uppercase tracking-widest text-cantera-600">Instrumento afectado</p>
                                        <p class="mt-1 text-xl font-black {{ $alertaTexto }}">{{ $alerta->activo->nombre }}</p>
                                    </div>
                                @endif
                                <p class="text-xs font-black uppercase tracking-widest {{ $alertaTexto }}">{{ $alertaTipo }}</p>
                                <p class="mt-2 text-lg font-black text-anil-900">{{ $alerta->titulo }}</p>
                                <p class="mt-1 text-sm text-anil-700">{{ $alerta->descripcion }}</p>
                                @if ($alerta->tipo === 'Baja y Adquisicion' && is_array($alerta->datos))
                                    <div class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                                        <div class="rounded-xl bg-hueso-50 p-3">
                                            <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Valor original</p>
                                            <p class="mt-1 font-black text-anil-900">${{ number_format((float) ($alerta->datos['valor_original'] ?? 0), 2) }} MXN</p>
                                        </div>
                                        <div class="rounded-xl bg-hueso-50 p-3">
                                            <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Reparaciones</p>
                                            <p class="mt-1 font-black text-anil-900">${{ number_format((float) ($alerta->datos['costo_reparaciones'] ?? 0), 2) }} MXN</p>
                                        </div>
                                        <div class="rounded-xl bg-hueso-50 p-3">
                                            <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Límite</p>
                                            <p class="mt-1 font-black text-anil-900">{{ $alerta->datos['porcentaje_limite'] ?? 60 }}%</p>
                                        </div>
                                    </div>
                                @elseif ($alerta->tipo === 'Preparacion de Temporada' && is_array($alerta->datos))
                                    <div class="mt-4 rounded-xl bg-hueso-50 p-3 text-sm">
                                        <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Base de alerta</p>
                                        @if (! empty($alerta->datos['temporada_base']))
                                            <p class="mt-1 font-bold text-anil-900">{{ $alerta->datos['temporada_base']['nombre'] }}: {{ $alerta->datos['temporada_base']['rango'] }}</p>
                                        @else
                                            <p class="mt-1 font-bold text-anil-900">Sin calendario base asociado.</p>
                                        @endif
                                        @if (! empty($alerta->datos['recursos']))
                                            <div class="mt-3 space-y-2">
                                                <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Recursos sugeridos (top {{ $alerta->datos['limite_recursos'] ?? 5 }})</p>
                                                @foreach ($alerta->datos['recursos'] as $recurso)
                                                    <div class="rounded-lg bg-anil-50 px-3 py-2">
                                                        <p class="font-black text-anil-900">{{ $recurso['nombre'] }}</p>
                                                        <p class="text-xs text-anil-700">{{ $recurso['categoria'] }} - {{ $recurso['total_prestamos'] }} préstamos</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @elseif ($alerta->tipo === 'Incremento Historico de Prestamos' && is_array($alerta->datos))
                                    <div class="mt-4 rounded-xl bg-hueso-50 p-3 text-sm">
                                        <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Comparación histórica</p>
                                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <div class="rounded-lg bg-cantera-50 px-3 py-2">
                                                <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Periodo anterior</p>
                                                <p class="mt-1 font-black text-cantera-900">{{ $alerta->datos['prestamos_anteriores'] ?? 0 }} préstamos</p>
                                                @if (! empty($alerta->datos['periodo_anterior']))
                                                    <p class="text-xs text-cantera-700">{{ $alerta->datos['periodo_anterior']['inicio'] }} a {{ $alerta->datos['periodo_anterior']['fin'] }}</p>
                                                @endif
                                            </div>
                                            <div class="rounded-lg bg-cantera-50 px-3 py-2">
                                                <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Periodo reciente</p>
                                                <p class="mt-1 font-black text-cantera-900">{{ $alerta->datos['prestamos_actuales'] ?? 0 }} préstamos</p>
                                                @if (! empty($alerta->datos['periodo_actual']))
                                                    <p class="text-xs text-cantera-700">{{ $alerta->datos['periodo_actual']['inicio'] }} a {{ $alerta->datos['periodo_actual']['fin'] }}</p>
                                                @endif
                                            </div>
                                            <div class="rounded-lg bg-cantera-50 px-3 py-2">
                                                <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Diferencia</p>
                                                <p class="mt-1 font-black text-cantera-900">{{ $alerta->datos['diferencia_prestamos'] ?? 0 }} préstamos</p>
                                            </div>
                                            <div class="rounded-lg bg-cantera-50 px-3 py-2">
                                                <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Incremento</p>
                                                <p class="mt-1 font-black text-cantera-900">{{ $alerta->datos['incremento_porcentaje'] ?? 0 }}%</p>
                                            </div>
                                            <div class="rounded-lg bg-cantera-50 px-3 py-2">
                                                <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Factor</p>
                                                <p class="mt-1 font-black text-cantera-900">{{ $alerta->datos['factor_crecimiento'] ?? 0 }}x</p>
                                            </div>
                                            <div class="rounded-lg bg-cantera-50 px-3 py-2">
                                                <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Regla</p>
                                                <p class="mt-1 font-black text-cantera-900">{{ $alerta->datos['umbral_incremento'] ?? 25 }}% mínimo</p>
                                                <p class="text-xs text-cantera-700">Muestra mínima: {{ $alerta->datos['minimo_prestamos_anteriores'] ?? 2 }} anteriores y {{ $alerta->datos['minimo_prestamos_actuales'] ?? 3 }} recientes</p>
                                            </div>
                                        </div>
                                        @if (! empty($alerta->datos['recursos']))
                                            <div class="mt-3 space-y-2">
                                                <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Recursos sugeridos (top {{ $alerta->datos['limite_recursos'] ?? 5 }})</p>
                                                @foreach ($alerta->datos['recursos'] as $recurso)
                                                    <div class="rounded-lg bg-cantera-50 px-3 py-2">
                                                        <p class="font-black text-cantera-900">{{ $recurso['nombre'] }}</p>
                                                        <p class="text-xs text-cantera-700">{{ $recurso['categoria'] }} - {{ $recurso['total_prestamos'] }} préstamos</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @elseif ($alerta->tipo === 'Fragilidad/Mal Uso' && is_array($alerta->datos))
                                    <div class="mt-4 rounded-xl bg-hueso-50 p-3 text-sm">
                                        <p class="text-xs font-black uppercase tracking-widest text-cantera-500">Daños recientes</p>
                                        <p class="mt-1 font-bold text-anil-900">{{ $alerta->datos['danios_recientes'] ?? 0 }} en {{ $alerta->datos['periodo_dias'] ?? 90 }} días</p>
                                    </div>
                                @endif

                                <form action="{{ route('alertas.resolver', $alerta->id_alerta) }}" method="POST" class="mt-4 text-right">
                                    @csrf
                                    <button type="submit" class="btn-soft">
                                        Marcar resuelta
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-[1.1fr_0.9fr] gap-8">
                <section class="module-card overflow-hidden">
                    <h3 class="text-2xl font-black mb-6 text-anil-900 border-b border-cantera-100 pb-4 uppercase tracking-tighter">Acciones operativas</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('activos.index') }}" class="flex flex-col items-center p-6 bg-anil-50 rounded-xl border border-anil-100 hover:bg-anil-100 hover:scale-105 hover:shadow-md transition transform text-center">
                            <span class="text-4xl mb-3">Catálogo</span>
                            <p class="font-black text-anil-900 uppercase tracking-widest text-sm">Inventario</p>
                            <p class="text-xs text-anil-700 mt-1">Consultar y prestar instrumentos</p>
                        </a>

                        <a href="{{ route('prestamos.create') }}" class="flex flex-col items-center p-6 bg-cantera-50 rounded-xl border border-cantera-100 hover:bg-cantera-100 hover:scale-105 hover:shadow-md transition transform text-center">
                            <span class="text-4xl mb-3">Préstamo</span>
                            <p class="font-black text-cantera-900 uppercase tracking-widest text-sm">Nuevo préstamo</p>
                            <p class="text-xs text-cantera-700 mt-1">Registrar salida con fecha y hora automáticas</p>
                        </a>

                        <a href="{{ route('prestamos.activos') }}" class="flex flex-col items-center p-6 bg-oxido-50 rounded-xl border border-oxido-100 hover:bg-oxido-100 hover:scale-105 hover:shadow-md transition transform text-center">
                            <span class="text-4xl mb-3">Devolución</span>
                            <p class="font-black text-oxido-900 uppercase tracking-widest text-sm">Devoluciones</p>
                            <p class="text-xs text-oxido-700 mt-1">Recibir equipo y registrar incidencias</p>
                        </a>

                        <a href="{{ route('mantenimientos.index') }}" class="flex flex-col items-center p-6 bg-ocre-50 rounded-xl border border-ocre-100 hover:bg-ocre-100 hover:scale-105 hover:shadow-md transition transform text-center">
                            <span class="text-4xl mb-3">Servicio</span>
                            <p class="font-black text-ocre-900 uppercase tracking-widest text-sm">Mantenimiento</p>
                            <p class="text-xs text-ocre-700 mt-1">Atender reparaciones y liberar equipo</p>
                        </a>

                        <a href="{{ route('temporadas-base.index') }}" class="flex flex-col items-center p-6 bg-anil-50 rounded-xl border border-anil-100 hover:bg-anil-100 hover:scale-105 hover:shadow-md transition transform text-center">
                            <span class="text-4xl mb-3">Calendario</span>
                            <p class="font-black text-anil-900 uppercase tracking-widest text-sm">Fechas base</p>
                            <p class="text-xs text-anil-700 mt-1">Administrar temporadas y alertas preventivas</p>
                        </a>
                    </div>
                </section>

                <section class="module-card overflow-hidden">
                    <div class="flex items-center justify-between gap-4 border-b border-cantera-100 pb-4">
                        <div>
                            <h3 class="text-2xl font-black text-anil-900 uppercase tracking-tighter">Panel de métricas</h3>
                            <p class="text-sm text-cantera-600 mt-1">Instrumentos con mayor demanda por número de préstamos registrados.</p>
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
                                        <p class="font-black text-anil-900">{{ $item->nombre }}</p>
                                        <p class="text-xs font-bold uppercase tracking-widest text-cantera-600">{{ $item->categoria }}</p>
                                    </div>
                                    <p class="text-sm font-black text-cultura-700">{{ $item->total_prestamos }} préstamos</p>
                                </div>
                                <div class="h-4 rounded-full bg-hueso-200 overflow-hidden">
                                    <div class="h-full rounded-full bg-cantera-600" style="width: {{ $anchoBarra }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border-2 border-dashed border-cantera-200 bg-hueso-50 py-12 text-center">
                                <p class="font-bold text-cantera-600">Todavía no hay datos suficientes para mostrar métricas.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
