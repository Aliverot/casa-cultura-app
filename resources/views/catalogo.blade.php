<x-app-layout>
    <div class="py-12 bg-hueso-100 min-h-screen">
        <div class="module-page-shell">
            <x-module-nav current="catalogo" />

                        <div class="mb-8 flex justify-center">
                <form action="{{ route('activos.index') }}" method="GET" class="w-full md:w-1/2 flex shadow-md rounded-md overflow-hidden">
                    <input
                        type="text"
                        name="buscar"
                        value="{{ request('buscar') }}"
                        placeholder="Buscar por nombre, modelo o código QR..."
                        class="w-full border-cantera-300 bg-hueso-50 text-anil-900 placeholder-cantera-500 p-4 text-lg focus:ring-2 focus:ring-ocre-400 focus:outline-none"
                    >
                    <button type="submit" class="btn-primary rounded-none">
                        Buscar
                    </button>
                </form>
            </div>

            @if ($errors->any())
                <div class="mb-8 bg-oxido-50 border-l-4 border-oxido-500 text-oxido-800 p-4 rounded-r shadow-sm">
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
                            'Disponible' => ['border-cantera-500', 'bg-cantera-600 text-hueso-50'],
                            'Mantenimiento' => ['border-ocre-500', 'bg-ocre-500 text-anil-900'],
                            'Extraviado' => ['border-oxido-500', 'bg-oxido-500 text-hueso-50'],
                            'Baja' => ['border-anil-400', 'bg-anil-700 text-hueso-50'],
                            default => ['border-oxido-500', 'bg-oxido-600 text-hueso-50'],
                        };

                        $mensajeEstado = match ($item->estado_actual) {
                            'No disponible' => 'Este instrumento ya se encuentra prestado y no puede prestarse de nuevo por ahora.',
                            'Mantenimiento' => 'Este instrumento está en mantenimiento y no está disponible para préstamo.',
                            'Extraviado' => 'Este instrumento no está disponible para préstamo.',
                            'Baja' => 'Este instrumento fue dado de baja y ya no está disponible para préstamo.',
                            default => 'Este instrumento no está disponible para préstamo.',
                        };
                    @endphp

                    <div class="bg-hueso-50 overflow-hidden shadow-xl sm:rounded-3xl p-8 border-t-8 {{ $estadoBorde }} border-x border-b border-cantera-200">
                        <div class="flex flex-col md:flex-row items-start md:items-center gap-8">
                            <div class="flex flex-col items-center space-y-4 bg-hueso-50 p-4 rounded-2xl border border-cantera-100">
                                <div class="bg-hueso-50 p-3 rounded-xl shadow-inner border border-cantera-200" id="qr-{{ $item->id_activo }}">
                                    {!! QrCode::size(140)->generate($item->codigo_qr) !!}
                                </div>
                                <button onclick="downloadQR('qr-{{ $item->id_activo }}', '{{ $item->codigo_qr }}')" class="text-sm bg-hueso-50 hover:bg-hueso-100 text-cultura-600 px-5 py-2 rounded-full border border-cultura-200 shadow-sm transition font-black uppercase tracking-tighter">
                                    Descargar QR
                                </button>
                            </div>

                            <div class="flex-grow space-y-3">
                                <h3 class="text-3xl font-black text-anil-900 tracking-tight">{{ $item->nombre }}</h3>
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="text-sm font-mono bg-cultura-50 text-cultura-700 px-3 py-1 rounded-md font-bold border border-cultura-100 italic">ID: {{ $item->codigo_qr }}</span>
                                    <span class="text-sm text-cantera-600 font-bold uppercase tracking-widest">{{ $item->categoria }}</span>
                                </div>

                                <div class="mt-4">
                                    <span class="px-6 py-2 rounded-full text-sm font-black uppercase tracking-widest shadow-sm {{ $estadoBadge }}">
                                        {{ $item->estado_actual }}
                                    </span>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-black uppercase tracking-widest text-slate-700">
                                        {{ $item->estadoCondicionLegible() }}
                                    </span>
                                    @if ($item->modelo)
                                        <span class="text-xs font-bold uppercase tracking-widest text-cantera-600">Modelo: {{ $item->modelo }}</span>
                                    @endif
                                </div>

                                <div class="mt-4 text-cantera-700 text-sm italic">
                                    Uso acumulado:
                                    <span class="font-bold text-anil-900">{{ number_format($item->horas_uso, 2) }} horas</span>
                                </div>

                                <div class="pt-2">
                                    <a href="{{ route('activos.edit', $item->id_activo) }}" class="inline-flex items-center rounded-lg border border-cultura-200 bg-cultura-50 px-4 py-2 text-xs font-black uppercase tracking-widest text-cultura-700 transition hover:bg-cultura-100">
                                        Modificar artículo
                                    </a>
                                </div>
                            </div>

                            <div class="w-full md:w-96 bg-hueso-50 p-6 rounded-2xl border border-cantera-200 shadow-inner">
                                @if ($item->estado_actual === 'Disponible')
                                    <form action="{{ route('prestamos.store') }}" method="POST" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="id_activo" value="{{ $item->id_activo }}">

                                        <div>
                                            <label class="text-xs font-black text-cantera-600 uppercase tracking-widest">Nombre del solicitante</label>
                                            <input
                                                type="text"
                                                name="nombre_solicitante"
                                                required
                                                autocomplete="name"
                                                data-full-name
                                                pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+(?:[ '-][A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*(?:\s+[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+(?:[ '-][A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*)+"
                                                title="Escribe nombre completo, solo con letras y espacios."
                                                placeholder="Nombre completo"
                                                class="block w-full rounded-lg border-cantera-300 bg-hueso-50 text-anil-900 p-3 mt-1 focus:ring-2 focus:ring-ocre-400 focus:border-ocre-500 shadow-sm"
                                            >
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-cantera-600 uppercase tracking-widest">Teléfono del solicitante</label>
                                            <input
                                                type="tel"
                                                name="contacto_solicitante"
                                                required
                                                inputmode="numeric"
                                                autocomplete="tel"
                                                minlength="10"
                                                maxlength="10"
                                                pattern="[0-9]{10}"
                                                data-digits-only
                                                title="Escribe un teléfono de 10 dígitos."
                                                placeholder="10 dígitos"
                                                class="block w-full rounded-lg border-cantera-300 bg-hueso-50 text-anil-900 p-3 mt-1 focus:ring-2 focus:ring-ocre-400 focus:border-ocre-500 shadow-sm"
                                            >
                                        </div>

                                        <div class="rounded-xl border border-cultura-100 bg-hueso-50 px-4 py-3">
                                            <p class="text-xs font-black text-cantera-600 uppercase tracking-widest">Salida automática</p>
                                            <p class="text-lg font-black text-anil-800 mt-1">{{ now()->format('d/m/Y H:i') }}</p>
                                            <p class="text-xs text-cantera-600 mt-1">La fecha y hora exactas se guardan automáticamente al confirmar.</p>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-cantera-600 uppercase tracking-widest">Condiciones iniciales</label>
                                            <textarea
                                                name="condiciones_entrega"
                                                required
                                                rows="3"
                                                minlength="8"
                                                maxlength="2000"
                                                data-no-long-digits
                                                placeholder="Ej. En perfectas condiciones, con estuche y correa."
                                                class="block w-full rounded-lg border-cantera-300 bg-hueso-50 text-anil-900 p-3 mt-1 focus:ring-2 focus:ring-ocre-400 focus:border-ocre-500 shadow-sm text-sm"
                                            ></textarea>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-cantera-600 uppercase tracking-widest">Devolución prevista</label>
                                            <input
                                                type="datetime-local"
                                                name="fecha_devolucion_prevista"
                                                required
                                                min="{{ now()->format('Y-m-d\TH:i') }}"
                                                class="block w-full rounded-lg border-cantera-300 bg-hueso-50 text-anil-900 p-3 mt-1 focus:ring-2 focus:ring-ocre-400 focus:border-ocre-500 shadow-sm text-sm"
                                            >
                                        </div>

                                        <button type="submit" class="btn-primary-wide">
                                            Confirmar préstamo
                                        </button>
                                    </form>

                                    <a href="{{ route('prestamos.create', ['id_activo' => $item->id_activo]) }}" class="mt-3 block text-center text-sm font-bold text-cultura-700 hover:text-cultura-900">
                                        Abrir formulario completo
                                    </a>
                                @else
                                    <div class="text-center py-10">
                                        <span class="inline-block bg-hueso-50 text-anil-700 font-black py-3 px-8 rounded-xl border-2 border-cantera-200 uppercase text-sm shadow-sm">
                                            {{ $item->estado_actual }}
                                        </span>
                                        <p class="text-sm text-cantera-600 mt-4">{{ $mensajeEstado }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-20 bg-hueso-50 rounded-3xl shadow-inner border-2 border-dashed border-cantera-200">
                        <p class="text-cantera-500 text-2xl font-medium">No se encontraron instrumentos.</p>
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
