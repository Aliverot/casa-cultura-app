<x-app-layout>
    <div class="min-h-screen bg-hueso-100 py-12">
        <div class="module-page-shell">
            <x-module-nav current="catalogo" />

            @php
                $estadoCondicionFormulario = old('estado_condicion', $activo->estadoCondicionEfectiva());
                $estadoSoloLectura = $activo->tieneEstadoOperativoProtegido();
            @endphp

            @if ($errors->any())
                <div class="mb-8 rounded-r border-l-4 border-oxido-500 bg-oxido-50 p-4 text-oxido-800 shadow-sm">
                    <p class="mb-2 font-black uppercase tracking-widest">No se pudo actualizar el artículo</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card overflow-hidden">
                <div class="mb-6 border-b border-cantera-100 pb-4">
                    <p class="text-xs font-black uppercase tracking-widest text-cantera-600">Artículo</p>
                    <h3 class="mt-2 text-2xl font-black text-anil-900">{{ $activo->nombre }}</h3>
                    <p class="mt-1 text-sm text-cantera-600">Código QR: {{ $activo->codigo_qr }}</p>
                </div>

                <form action="{{ route('activos.update', $activo->id_activo) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-anil-700">Nombre del instrumento o equipo</label>
                        <input
                            type="text"
                            name="nombre"
                            value="{{ old('nombre', $activo->nombre) }}"
                            required
                            class="mt-1 block w-full rounded-xl border-cantera-300 shadow-sm focus:border-ocre-500 focus:ring-ocre-400"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-anil-700">Modelo o referencia</label>
                        <input
                            type="text"
                            name="modelo"
                            value="{{ old('modelo', $activo->modelo) }}"
                            class="mt-1 block w-full rounded-xl border-cantera-300 shadow-sm focus:border-ocre-500 focus:ring-ocre-400"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-anil-700">Categoría</label>
                        <select name="categoria" required class="mt-1 block w-full rounded-xl border-cantera-300 shadow-sm focus:border-ocre-500 focus:ring-ocre-400">
                            <option value="Instrumentos de Cuerda" @selected(old('categoria', $activo->categoria) === 'Instrumentos de Cuerda')>Instrumentos de Cuerda</option>
                            <option value="Instrumentos de Viento" @selected(old('categoria', $activo->categoria) === 'Instrumentos de Viento')>Instrumentos de Viento</option>
                            <option value="Percusiones" @selected(old('categoria', $activo->categoria) === 'Percusiones')>Percusiones</option>
                            <option value="Vestuario/Danza" @selected(old('categoria', $activo->categoria) === 'Vestuario/Danza')>Vestuario / Danza</option>
                            <option value="Equipo de Sonido" @selected(old('categoria', $activo->categoria) === 'Equipo de Sonido')>Equipo de Sonido</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-anil-700">Límite de horas para mantenimiento</label>
                        <input
                            type="number"
                            name="limite_mantenimiento"
                            value="{{ old('limite_mantenimiento', $activo->limite_mantenimiento) }}"
                            required
                            min="1"
                            step="0.01"
                            class="mt-1 block w-full rounded-xl border-cantera-300 shadow-sm focus:border-ocre-500 focus:ring-ocre-400"
                        >
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-anil-700">Valor original aproximado</label>
                            <div class="relative mt-1">
                                <span class="absolute left-4 top-3 text-cantera-600">$</span>
                                <input
                                    type="number"
                                    name="valor_original"
                                    value="{{ old('valor_original', $activo->valor_original) }}"
                                    min="0"
                                    step="0.01"
                                    class="block w-full rounded-xl border-cantera-300 pl-9 pr-20 shadow-sm focus:border-ocre-500 focus:ring-ocre-400"
                                >
                                <span class="absolute right-4 top-3 text-xs font-black uppercase tracking-widest text-cantera-600">MXN</span>
                            </div>
                        </div>

                        <div>
                            @if ($estadoSoloLectura)
                                <label class="block text-sm font-medium text-anil-700">Estado actual</label>
                                <input
                                    type="text"
                                    value="{{ $activo->estadoOperativoResumen() }}"
                                    readonly
                                    class="mt-1 block w-full rounded-xl border-cantera-300 bg-hueso-100 text-anil-900 shadow-sm"
                                >
                                <input type="hidden" name="estado_condicion" value="{{ $estadoCondicionFormulario }}">
                                <p class="mt-1 text-xs text-cantera-600">Este estado se controla por préstamo, mantenimiento o baja.</p>
                            @else
                                <label class="block text-sm font-medium text-anil-700">Estado estandarizado</label>
                                <select name="estado_condicion" required class="mt-1 block w-full rounded-xl border-cantera-300 shadow-sm focus:border-ocre-500 focus:ring-ocre-400">
                                    @foreach ($estadosCondicion as $estadoCondicion)
                                        <option value="{{ $estadoCondicion }}" @selected($estadoCondicionFormulario === $estadoCondicion)>
                                            {{ \App\Models\Activo::etiquetaEstadoCondicion($estadoCondicion) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-2xl border border-cantera-200 bg-hueso-50 p-4">
                        <p class="text-xs font-black uppercase tracking-widest text-cantera-600">Estado actual</p>
                        <p class="mt-2 text-base font-bold text-anil-900">{{ $activo->estadoActualLegible() }}</p>
                        <p class="mt-1 text-sm text-cantera-700">
                            {{ $estadoSoloLectura ? 'Situación operativa' : 'Condición operativa' }}:
                            <span class="font-bold text-anil-900">{{ $activo->estadoOperativoResumen() }}</span>
                        </p>
                        @if ($activo->estado_actual === 'Baja')
                            <p class="mt-1 text-sm text-cantera-700">Este artículo ya fue dado de baja. Solo se permiten correcciones de registro y consulta de historial.</p>
                        @else
                            <p class="mt-1 text-sm text-cantera-700">Las modificaciones del artículo no alteran su historial de préstamos ni mantenimientos.</p>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('activos.index') }}" class="btn-cancel">Cancelar</a>
                        <button type="submit" class="btn-primary">
                            Guardar cambios
                        </button>
                    </div>
                </form>

                @if ($activo->estado_actual !== 'Baja')
                    <form action="{{ route('activos.baja', $activo->id_activo) }}" method="POST" class="mt-6 border-t border-cantera-100 pt-6">
                        @csrf
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-black text-oxido-700">Dar de baja artículo</p>
                                <p class="text-sm text-cantera-600">Esta acción conserva el historial y evita que el instrumento vuelva a prestarse.</p>
                            </div>
                            <button type="submit" class="btn-danger">
                                Dar de baja
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
