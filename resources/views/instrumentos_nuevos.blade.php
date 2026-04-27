<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nuevo Instrumento / Activo') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-100 py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <x-module-nav current="instrumentos" />

            @if ($errors->any())
                <div class="mb-8 rounded-r border-l-4 border-red-500 bg-red-100 p-4 text-red-800 shadow-sm">
                    <p class="mb-2 font-black uppercase tracking-widest">No se pudo guardar el instrumento</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 shadow-xl">
                <form action="{{ route('activos.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre del instrumento o equipo</label>
                        <input
                            type="text"
                            name="nombre"
                            value="{{ old('nombre') }}"
                            required
                            placeholder="Ej. Guitarra acustica Yamaha"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-cultura-500 focus:ring-cultura-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Categoria</label>
                        <select name="categoria" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-cultura-500 focus:ring-cultura-500">
                            <option value="Instrumentos de Cuerda" @selected(old('categoria') === 'Instrumentos de Cuerda')>Instrumentos de Cuerda</option>
                            <option value="Instrumentos de Viento" @selected(old('categoria') === 'Instrumentos de Viento')>Instrumentos de Viento</option>
                            <option value="Percusiones" @selected(old('categoria') === 'Percusiones')>Percusiones</option>
                            <option value="Vestuario/Danza" @selected(old('categoria') === 'Vestuario/Danza')>Vestuario / Danza</option>
                            <option value="Equipo de Sonido" @selected(old('categoria') === 'Equipo de Sonido')>Equipo de Sonido</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Limite de horas para mantenimiento</label>
                        <input
                            type="number"
                            name="limite_mantenimiento"
                            value="{{ old('limite_mantenimiento') }}"
                            required
                            min="1"
                            step="0.01"
                            placeholder="Ej. 100"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-cultura-500 focus:ring-cultura-500"
                        >
                        <p class="mt-1 text-xs text-gray-500">Horas estimadas de uso antes de requerir revision, afinacion o limpieza profunda.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-xl bg-cultura-600 px-6 py-3 font-bold text-white shadow transition hover:bg-cultura-700">
                            Guardar en Inventario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
