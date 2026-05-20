<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Calendario de Temporadas') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-100 py-12">
        <div class="module-page-shell">
            <x-module-nav current="temporadas" />

            @if ($errors->any())
                <div class="mb-8 rounded-r border-l-4 border-red-500 bg-red-100 p-4 text-red-800 shadow-sm">
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
                    <div class="border-b border-gray-100 pb-4">
                        <h3 class="text-2xl font-black uppercase tracking-tight text-gray-900">Agregar Temporada</h3>
                        <p class="mt-1 text-sm text-gray-500">Registra una fecha anual para que el sistema prepare mantenimiento preventivo antes de que llegue.</p>
                    </div>

                    <form action="{{ route('temporadas-base.store') }}" method="POST" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Nombre</label>
                            <input
                                type="text"
                                name="nombre"
                                value="{{ old('nombre') }}"
                                required
                                placeholder="Ej. Festival cultural de verano"
                                class="w-full rounded-xl border-gray-300 bg-white p-4 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                            >
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Inicio</label>
                                <input
                                    type="text"
                                    name="fecha_inicio"
                                    value="{{ old('fecha_inicio') }}"
                                    required
                                    maxlength="5"
                                    pattern="[0-9]{2}-[0-9]{2}"
                                    placeholder="MM-DD"
                                    class="w-full rounded-xl border-gray-300 bg-white p-4 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Fin</label>
                                <input
                                    type="text"
                                    name="fecha_fin"
                                    value="{{ old('fecha_fin') }}"
                                    required
                                    maxlength="5"
                                    pattern="[0-9]{2}-[0-9]{2}"
                                    placeholder="MM-DD"
                                    class="w-full rounded-xl border-gray-300 bg-white p-4 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-500">Dias de anticipacion</label>
                            <input
                                type="number"
                                name="dias_anticipacion"
                                value="{{ old('dias_anticipacion', 30) }}"
                                required
                                min="0"
                                max="365"
                                class="w-full rounded-xl border-gray-300 bg-white p-4 text-base text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                            >
                        </div>

                        <label class="flex items-center gap-3 rounded-2xl border border-blue-100 bg-blue-50 p-4">
                            <input type="checkbox" name="activa" value="1" class="rounded border-gray-300 text-cultura-600 focus:ring-cultura-500" @checked(old('activa', '1') === '1')>
                            <span class="text-sm font-semibold text-blue-900">Usar esta fecha para generar alertas de temporada.</span>
                        </label>

                        <button type="submit" class="w-full rounded-xl bg-cultura-600 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg transition hover:bg-cultura-700">
                            Agregar fecha
                        </button>
                    </form>
                </section>

                <section class="module-card">
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-4">
                        <div>
                            <h3 class="text-2xl font-black uppercase tracking-tight text-gray-900">Temporadas Registradas</h3>
                            <p class="mt-1 text-sm text-gray-500">Las fechas activas se usan junto con el incremento reciente de prestamos.</p>
                        </div>
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-blue-700">
                            {{ $temporadas->count() }} fechas
                        </span>
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse ($temporadas as $temporada)
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                                <form action="{{ route('temporadas-base.update', $temporada->id_temporada) }}" method="POST" class="space-y-4">
                                    @csrf
                                    @method('PATCH')

                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-lg font-black text-gray-900">{{ $temporada->nombre }}</p>
                                        @if ($temporada->activa)
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-green-700">Activa</span>
                                        @else
                                            <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-black uppercase tracking-widest text-gray-600">Inactiva</span>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[1.4fr_0.55fr_0.55fr_0.55fr]">
                                        <div>
                                            <label class="mb-1 block text-xs font-black uppercase tracking-widest text-gray-400">Nombre</label>
                                            <input
                                                type="text"
                                                name="nombre"
                                                value="{{ old('nombre', $temporada->nombre) }}"
                                                required
                                                class="w-full rounded-xl border-gray-300 bg-white p-3 text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-xs font-black uppercase tracking-widest text-gray-400">Inicio</label>
                                            <input
                                                type="text"
                                                name="fecha_inicio"
                                                value="{{ old('fecha_inicio', $temporada->fecha_inicio) }}"
                                                required
                                                maxlength="5"
                                                pattern="[0-9]{2}-[0-9]{2}"
                                                class="w-full rounded-xl border-gray-300 bg-white p-3 text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-xs font-black uppercase tracking-widest text-gray-400">Fin</label>
                                            <input
                                                type="text"
                                                name="fecha_fin"
                                                value="{{ old('fecha_fin', $temporada->fecha_fin) }}"
                                                required
                                                maxlength="5"
                                                pattern="[0-9]{2}-[0-9]{2}"
                                                class="w-full rounded-xl border-gray-300 bg-white p-3 text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-xs font-black uppercase tracking-widest text-gray-400">Dias</label>
                                            <input
                                                type="number"
                                                name="dias_anticipacion"
                                                value="{{ old('dias_anticipacion', $temporada->dias_anticipacion) }}"
                                                required
                                                min="0"
                                                max="365"
                                                class="w-full rounded-xl border-gray-300 bg-white p-3 text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-cultura-500"
                                            >
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3">
                                            <input type="checkbox" name="activa" value="1" class="rounded border-gray-300 text-cultura-600 focus:ring-cultura-500" @checked($temporada->activa)>
                                            <span class="text-sm font-semibold text-gray-700">Activa</span>
                                        </label>

                                        <div class="flex flex-wrap gap-3">
                                            <button type="submit" class="rounded-lg bg-cultura-600 px-4 py-2 text-xs font-black uppercase tracking-widest text-white shadow-sm transition hover:bg-cultura-700">
                                                Guardar cambios
                                            </button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        @empty
                            <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 py-12 text-center">
                                <p class="font-bold text-gray-500">Todavia no hay fechas de temporada registradas.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
