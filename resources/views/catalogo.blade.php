<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Gestion de Inventario - Casa de la Cultura') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-module-nav current="catalogo" />

            <div class="mb-8 flex justify-center">
                <form action="{{ route('activos.index') }}" method="GET" class="w-full md:w-1/2 flex shadow-md rounded-md overflow-hidden">
                    <input
                        type="text"
                        name="buscar"
                        value="{{ request('buscar') }}"
                        placeholder="Buscar por nombre o codigo QR..."
                        class="w-full border-gray-300 bg-white text-gray-900 placeholder-gray-400 p-4 text-lg focus:ring-2 focus:ring-cultura-500 focus:outline-none"
                    >
                    <button type="submit" class="bg-cultura-600 hover:bg-cultura-700 text-white px-8 py-2 font-black transition uppercase tracking-widest">
                        Buscar
                    </button>
                </form>
            </div>

            @if ($errors->any())
                <div class="mb-8 bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-r shadow-sm">
                    <p class="font-black uppercase tracking-widest mb-2">No se pudo procesar la solicitud</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-8">
                @forelse ($instrumentos as $item)
                    @php
                        [$estadoBorde, $estadoBadge] = match ($item->estado_actual) {
                            'Disponible' => ['border-green-500', 'bg-green-500 text-white'],
                            'Mantenimiento' => ['border-amber-500', 'bg-amber-500 text-white'],
                            default => ['border-red-500', 'bg-red-500 text-white'],
                        };
                    @endphp

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl p-8 border-t-8 {{ $estadoBorde }} border-x border-b border-gray-200">
                        <div class="flex flex-col md:flex-row items-start md:items-center gap-8">
                            <div class="flex flex-col items-center space-y-4 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <div class="bg-white p-3 rounded-xl shadow-inner border border-gray-200" id="qr-{{ $item->id_activo }}">
                                    {!! QrCode::size(140)->generate($item->codigo_qr) !!}
                                </div>
                                <button onclick="downloadQR('qr-{{ $item->id_activo }}', '{{ $item->codigo_qr }}')" class="text-sm bg-white hover:bg-gray-100 text-cultura-600 px-5 py-2 rounded-full border border-cultura-200 shadow-sm transition font-black uppercase tracking-tighter">
                                    Descargar QR
                                </button>
                            </div>

                            <div class="flex-grow space-y-3">
                                <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ $item->nombre }}</h3>
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="text-sm font-mono bg-cultura-50 text-cultura-700 px-3 py-1 rounded-md font-bold border border-cultura-100 italic">ID: {{ $item->codigo_qr }}</span>
                                    <span class="text-sm text-gray-500 font-bold uppercase tracking-widest">{{ $item->categoria }}</span>
                                </div>

                                <div class="mt-4">
                                    <span class="px-6 py-2 rounded-full text-sm font-black uppercase tracking-widest shadow-sm {{ $estadoBadge }}">
                                        {{ $item->estado_actual }}
                                    </span>
                                </div>

                                <div class="mt-4 text-gray-600 text-sm italic">
                                    Uso acumulado:
                                    <span class="font-bold text-gray-900">{{ number_format($item->horas_uso, 2) }} horas</span>
                                </div>
                            </div>

                            <div class="w-full md:w-96 bg-gray-50 p-6 rounded-2xl border border-gray-200 shadow-inner">
                                @if ($item->estado_actual === 'Disponible')
                                    <form action="{{ route('prestamos.store') }}" method="POST" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="id_activo" value="{{ $item->id_activo }}">

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">Nombre del solicitante</label>
                                            <input
                                                type="text"
                                                name="nombre_solicitante"
                                                required
                                                placeholder="Nombre completo"
                                                class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 p-3 mt-1 focus:ring-2 focus:ring-cultura-500 focus:border-cultura-500 shadow-sm"
                                            >
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">Contacto o identificador</label>
                                            <input
                                                type="text"
                                                name="contacto_solicitante"
                                                required
                                                placeholder="Telefono, matricula o control"
                                                class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 p-3 mt-1 focus:ring-2 focus:ring-cultura-500 focus:border-cultura-500 shadow-sm"
                                            >
                                        </div>

                                        <div class="rounded-xl border border-cultura-100 bg-white px-4 py-3">
                                            <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Salida automatica</p>
                                            <p class="text-lg font-black text-gray-800 mt-1">{{ now()->format('d/m/Y H:i') }}</p>
                                            <p class="text-xs text-gray-500 mt-1">La fecha y hora exactas se guardan automaticamente al confirmar.</p>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">Condiciones iniciales</label>
                                            <textarea
                                                name="condiciones_entrega"
                                                required
                                                rows="3"
                                                placeholder="Ej. En perfectas condiciones, con estuche y correa."
                                                class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 p-3 mt-1 focus:ring-2 focus:ring-cultura-500 focus:border-cultura-500 shadow-sm text-sm"
                                            ></textarea>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">Devolucion prevista</label>
                                            <input
                                                type="datetime-local"
                                                name="fecha_devolucion_prevista"
                                                required
                                                min="{{ now()->format('Y-m-d\TH:i') }}"
                                                class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 p-3 mt-1 focus:ring-2 focus:ring-cultura-500 focus:border-cultura-500 shadow-sm text-sm"
                                            >
                                        </div>

                                        <button type="submit" class="w-full bg-acento-principal hover:bg-acento-hover text-white font-black py-4 rounded-xl shadow-lg transition transform active:scale-95 text-lg uppercase tracking-widest">
                                            Confirmar Prestamo
                                        </button>
                                    </form>

                                    <a href="{{ route('prestamos.create', ['id_activo' => $item->id_activo]) }}" class="mt-3 block text-center text-sm font-bold text-cultura-700 hover:text-cultura-900">
                                        Abrir formulario completo
                                    </a>
                                @else
                                    <div class="text-center py-10">
                                        <span class="inline-block bg-white text-gray-700 font-black py-3 px-8 rounded-xl border-2 border-gray-200 uppercase text-sm shadow-sm">
                                            {{ $item->estado_actual }}
                                        </span>
                                        <p class="text-sm text-gray-500 mt-4">Este instrumento no puede prestarse mientras permanezca en este estado.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-20 bg-white rounded-3xl shadow-inner border-2 border-dashed border-gray-200">
                        <p class="text-gray-400 text-2xl font-medium">No se encontraron instrumentos.</p>
                        <a href="{{ route('activos.index') }}" class="text-cultura-600 font-bold underline mt-4 inline-block">Ver todos</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        function downloadQR(id, filename) {
            const svg = document.getElementById(id).querySelector('svg');
            const svgData = new XMLSerializer().serializeToString(svg);
            const canvas = document.createElement("canvas");
            const ctx = canvas.getContext("2d");
            const img = new Image();

            img.onload = function () {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);

                const downloadLink = document.createElement("a");
                downloadLink.download = `QR-${filename}.png`;
                downloadLink.href = canvas.toDataURL("image/png");
                downloadLink.click();
            };

            img.src = "data:image/svg+xml;base64," + btoa(svgData);
        }
    </script>
</x-app-layout>
