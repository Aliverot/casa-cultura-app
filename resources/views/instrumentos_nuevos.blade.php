<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-anil-800 leading-tight">
            {{ __('Registrar nuevo instrumento / activo') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-hueso-100 py-12">
        <div class="module-page-shell">
            <x-module-nav current="instrumentos" />

            @if ($errors->any())
                <div class="mb-8 rounded-r border-l-4 border-oxido-500 bg-oxido-50 p-4 text-oxido-800 shadow-sm">
                    <p class="mb-2 font-black uppercase tracking-widest">No se pudo guardar el instrumento</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card overflow-hidden">
                <form action="{{ route('activos.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-anil-700">Nombre del instrumento o equipo</label>
                        <input
                            type="text"
                            name="nombre"
                            value="{{ old('nombre') }}"
                            required
                            placeholder="Ej. Guitarra acústica Yamaha"
                            class="mt-1 block w-full rounded-xl border-cantera-300 shadow-sm focus:border-ocre-500 focus:ring-ocre-400"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-anil-700">Modelo o referencia</label>
                        <input
                            type="text"
                            name="modelo"
                            value="{{ old('modelo') }}"
                            placeholder="Ej. Yamaha C40, Huipil bordado regional"
                            class="mt-1 block w-full rounded-xl border-cantera-300 shadow-sm focus:border-ocre-500 focus:ring-ocre-400"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-anil-700">Categoría</label>
                        <select name="categoria" required class="mt-1 block w-full rounded-xl border-cantera-300 shadow-sm focus:border-ocre-500 focus:ring-ocre-400">
                            <option value="Instrumentos de Cuerda" @selected(old('categoria') === 'Instrumentos de Cuerda')>Instrumentos de Cuerda</option>
                            <option value="Instrumentos de Viento" @selected(old('categoria') === 'Instrumentos de Viento')>Instrumentos de Viento</option>
                            <option value="Percusiones" @selected(old('categoria') === 'Percusiones')>Percusiones</option>
                            <option value="Vestuario/Danza" @selected(old('categoria') === 'Vestuario/Danza')>Vestuario / Danza</option>
                            <option value="Equipo de Sonido" @selected(old('categoria') === 'Equipo de Sonido')>Equipo de Sonido</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-anil-700">Límite de horas para mantenimiento</label>
                        <input
                            type="number"
                            name="limite_mantenimiento"
                            value="{{ old('limite_mantenimiento') }}"
                            required
                            min="1"
                            step="0.01"
                            placeholder="Ej. 100"
                            class="mt-1 block w-full rounded-xl border-cantera-300 shadow-sm focus:border-ocre-500 focus:ring-ocre-400"
                        >
                        <p class="mt-1 text-xs text-cantera-600">Horas estimadas de uso antes de requerir revisión, afinación o limpieza profunda.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-anil-700">Valor original aproximado</label>
                            <div class="relative mt-1">
                                <span class="absolute left-4 top-3 text-cantera-600">$</span>
                                <input
                                    type="number"
                                    name="valor_original"
                                    value="{{ old('valor_original') }}"
                                    min="0"
                                    step="0.01"
                                    placeholder="Ej. 2500"
                                    class="block w-full rounded-xl border-cantera-300 pl-9 pr-20 shadow-sm focus:border-ocre-500 focus:ring-ocre-400"
                                >
                                <span class="absolute right-4 top-3 text-xs font-black uppercase tracking-widest text-cantera-600">MXN</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-anil-700">Estado estandarizado</label>
                            <select name="estado_condicion" required class="mt-1 block w-full rounded-xl border-cantera-300 shadow-sm focus:border-ocre-500 focus:ring-ocre-400">
                                @foreach ($estadosCondicion as $estadoCondicion)
                                    <option value="{{ $estadoCondicion }}" @selected(old('estado_condicion', 'Excelente') === $estadoCondicion)>
                                        {{ \App\Models\Activo::etiquetaEstadoCondicion($estadoCondicion) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-xl bg-cultura-600 px-6 py-3 font-bold text-hueso-50 shadow transition hover:bg-cultura-700">
                            Guardar en inventario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
