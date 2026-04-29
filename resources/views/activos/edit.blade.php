<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modificar Articulo del Inventario') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-100 py-12">
        <div class="module-page-shell">
            <x-module-nav current="catalogo" />

            @if ($errors->any())
                <div class="mb-8 rounded-r border-l-4 border-red-500 bg-red-100 p-4 text-red-800 shadow-sm">
                    <p class="mb-2 font-black uppercase tracking-widest">No se pudo actualizar el articulo</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card overflow-hidden">
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <p class="text-xs font-black uppercase tracking-widest text-gray-500">Articulo</p>
                    <h3 class="mt-2 text-2xl font-black text-gray-900">{{ $activo->nombre }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Codigo QR: {{ $activo->codigo_qr }}</p>
                </div>

                <form action="{{ route('activos.update', $activo->id_activo) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre del instrumento o equipo</label>
                        <input
                            type="text"
                            name="nombre"
                            value="{{ old('nombre', $activo->nombre) }}"
                            required
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-cultura-500 focus:ring-cultura-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Categoria</label>
                        <select name="categoria" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-cultura-500 focus:ring-cultura-500">
                            <option value="Instrumentos de Cuerda" @selected(old('categoria', $activo->categoria) === 'Instrumentos de Cuerda')>Instrumentos de Cuerda</option>
                            <option value="Instrumentos de Viento" @selected(old('categoria', $activo->categoria) === 'Instrumentos de Viento')>Instrumentos de Viento</option>
                            <option value="Percusiones" @selected(old('categoria', $activo->categoria) === 'Percusiones')>Percusiones</option>
                            <option value="Vestuario/Danza" @selected(old('categoria', $activo->categoria) === 'Vestuario/Danza')>Vestuario / Danza</option>
                            <option value="Equipo de Sonido" @selected(old('categoria', $activo->categoria) === 'Equipo de Sonido')>Equipo de Sonido</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Limite de horas para mantenimiento</label>
                        <input
                            type="number"
                            name="limite_mantenimiento"
                            value="{{ old('limite_mantenimiento', $activo->limite_mantenimiento) }}"
                            required
                            min="1"
                            step="0.01"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-cultura-500 focus:ring-cultura-500"
                        >
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-black uppercase tracking-widest text-gray-500">Estado actual</p>
                        <p class="mt-2 text-base font-bold text-gray-900">{{ $activo->estado_actual }}</p>
                        @if ($activo->estado_actual === 'Baja')
                            <p class="mt-1 text-sm text-gray-600">Este articulo ya fue dado de baja. Solo se permiten correcciones de registro y consulta de historial.</p>
                        @else
                            <p class="mt-1 text-sm text-gray-600">Las modificaciones del articulo no alteran su historial de prestamos ni mantenimientos.</p>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('activos.index') }}" class="text-sm font-bold text-gray-600 hover:text-gray-900">Cancelar</a>
                        <button type="submit" class="rounded-xl bg-cultura-600 px-6 py-3 text-sm font-black uppercase tracking-widest text-white shadow transition hover:bg-cultura-700">
                            Guardar cambios
                        </button>
                    </div>
                </form>

                @if ($activo->estado_actual !== 'Baja')
                    <form action="{{ route('activos.baja', $activo->id_activo) }}" method="POST" class="mt-6 border-t border-gray-100 pt-6">
                        @csrf
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-black text-red-700">Dar de baja articulo</p>
                                <p class="text-sm text-gray-500">Esta accion conserva el historial y evita que el instrumento vuelva a prestarse.</p>
                            </div>
                            <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-black uppercase tracking-widest text-red-700 transition hover:bg-red-100">
                                Dar de baja
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
