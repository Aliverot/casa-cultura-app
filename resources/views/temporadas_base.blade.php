<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-anil-800 leading-tight">
            {{ __('Calendario de temporadas') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-hueso-100 py-12">
        <div class="module-page-shell">
            <x-module-nav current="temporadas" />

            @if ($errors->any())
                <div class="mb-8 rounded-r border-l-4 border-oxido-500 bg-oxido-50 p-4 text-oxido-800 shadow-sm">
                    <p class="mb-2 font-black uppercase tracking-widest">No se pudo guardar la temporada</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-8 xl:grid-cols-[0.9fr_1.1fr]">
                <section class="module-card">
                    <div class="border-b border-cantera-100 pb-4">
                        <h3 class="text-2xl font-black uppercase tracking-tight text-anil-900">Agregar temporada</h3>
                        <p class="mt-1 text-sm text-cantera-600">Registra una fecha anual para que el sistema prepare mantenimiento preventivo antes de que llegue.</p>
                    </div>

                    <form action="{{ route('temporadas-base.store') }}" method="POST" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Nombre</label>
                            <input
                                type="text"
                                name="nombre"
                                value="{{ old('nombre') }}"
                                required
                                placeholder="Ej. Festival cultural de verano"
                                class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-4 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                            >
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Inicio</label>
                                <input
                                    type="text"
                                    name="fecha_inicio"
                                    value="{{ old('fecha_inicio') }}"
                                    required
                                    maxlength="5"
                                    pattern="[0-9]{2}-[0-9]{2}"
                                    placeholder="MM-DD"
                                    class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-4 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Fin</label>
                                <input
                                    type="text"
                                    name="fecha_fin"
                                    value="{{ old('fecha_fin') }}"
                                    required
                                    maxlength="5"
                                    pattern="[0-9]{2}-[0-9]{2}"
                                    placeholder="MM-DD"
                                    class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-4 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-widest text-cantera-600">Días de anticipación</label>
                            <input
                                type="number"
                                name="dias_anticipacion"
                                value="{{ old('dias_anticipacion', 30) }}"
                                required
                                min="0"
                                max="365"
                                class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-4 text-base text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                            >
                        </div>

                        <label class="flex items-center gap-3 rounded-2xl border border-anil-100 bg-anil-50 p-4">
                            <input type="checkbox" name="activa" value="1" class="rounded border-cantera-300 text-cultura-600 focus:ring-ocre-400" @checked(old('activa', '1') === '1')>
                            <span class="text-sm font-semibold text-anil-900">Usar esta fecha para generar alertas de temporada.</span>
                        </label>

                        <button type="submit" class="w-full rounded-xl bg-cultura-600 py-4 text-sm font-black uppercase tracking-widest text-hueso-50 shadow-lg transition hover:bg-cultura-700">
                            Agregar fecha
                        </button>
                    </form>
                </section>

                <section class="module-card">
                    <div class="flex items-center justify-between gap-4 border-b border-cantera-100 pb-4">
                        <div>
                            <h3 class="text-2xl font-black uppercase tracking-tight text-anil-900">Temporadas registradas</h3>
                            <p class="mt-1 text-sm text-cantera-600">Las fechas activas se usan junto con el incremento reciente de préstamos.</p>
                        </div>
                        <span class="rounded-full bg-anil-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-anil-700">
                            {{ $temporadas->count() }} fechas
                        </span>
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse ($temporadas as $temporada)
                            <div class="rounded-2xl border border-cantera-200 bg-hueso-50 p-5">
                                <form action="{{ route('temporadas-base.update', $temporada->id_temporada) }}" method="POST" class="space-y-4">
                                    @csrf
                                    @method('PATCH')

                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-lg font-black text-anil-900">{{ $temporada->nombre }}</p>
                                        @if ($temporada->activa)
                                            <span class="rounded-full bg-cantera-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-cantera-700">Activa</span>
                                        @else
                                            <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-black uppercase tracking-widest text-cantera-700">Inactiva</span>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[1.4fr_0.55fr_0.55fr_0.55fr]">
                                        <div>
                                            <label class="mb-1 block text-xs font-black uppercase tracking-widest text-cantera-500">Nombre</label>
                                            <input
                                                type="text"
                                                name="nombre"
                                                value="{{ old('nombre', $temporada->nombre) }}"
                                                required
                                                class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-3 text-sm text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-xs font-black uppercase tracking-widest text-cantera-500">Inicio</label>
                                            <input
                                                type="text"
                                                name="fecha_inicio"
                                                value="{{ old('fecha_inicio', $temporada->fecha_inicio) }}"
                                                required
                                                maxlength="5"
                                                pattern="[0-9]{2}-[0-9]{2}"
                                                class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-3 text-sm text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-xs font-black uppercase tracking-widest text-cantera-500">Fin</label>
                                            <input
                                                type="text"
                                                name="fecha_fin"
                                                value="{{ old('fecha_fin', $temporada->fecha_fin) }}"
                                                required
                                                maxlength="5"
                                                pattern="[0-9]{2}-[0-9]{2}"
                                                class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-3 text-sm text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-xs font-black uppercase tracking-widest text-cantera-500">Días</label>
                                            <input
                                                type="number"
                                                name="dias_anticipacion"
                                                value="{{ old('dias_anticipacion', $temporada->dias_anticipacion) }}"
                                                required
                                                min="0"
                                                max="365"
                                                class="w-full rounded-xl border-cantera-300 bg-hueso-50 p-3 text-sm text-anil-900 shadow-sm focus:ring-2 focus:ring-ocre-400"
                                            >
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <label class="flex items-center gap-3 rounded-xl border border-cantera-200 bg-hueso-50 px-4 py-3">
                                            <input type="checkbox" name="activa" value="1" class="rounded border-cantera-300 text-cultura-600 focus:ring-ocre-400" @checked($temporada->activa)>
                                            <span class="text-sm font-semibold text-anil-700">Activa</span>
                                        </label>

                                        <div class="flex flex-wrap gap-3">
                                            <button type="submit" class="rounded-lg bg-cultura-600 px-4 py-2 text-xs font-black uppercase tracking-widest text-hueso-50 shadow-sm transition hover:bg-cultura-700">
                                                Guardar cambios
                                            </button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        @empty
                            <div class="rounded-2xl border-2 border-dashed border-cantera-200 bg-hueso-50 py-12 text-center">
                                <p class="font-bold text-cantera-600">Todavía no hay fechas de temporada registradas.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
