<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Gestión de Inventario - Casa de la Cultura') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- MENÚ DE NAVEGACIÓN CORREGIDO -->
            <div class="bg-white p-4 rounded-lg flex flex-wrap justify-center gap-4 mb-6 shadow-sm border border-gray-200">
                
                <a href="{{ route('activos.index') }}" class="bg-cultura-600 text-white px-6 py-2 rounded-md shadow-md font-bold">Catálogo</a>

                <a href="{{ route('activos.create') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-cultura-600 hover:bg-gray-50 rounded-md transition">Instrumentos Nuevos</a>

                <a href="{{ route('prestamos.activos') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-cultura-600 hover:bg-gray-50 rounded-md transition">Devoluciones / Multas</a>

                <!-- Cambiamos mantenimiento.index por mantenimientos.index -->
                <a href="{{ route('mantenimientos.index') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-cultura-600 hover:bg-gray-50 rounded-md transition">Mantenimiento</a>

                <a href="{{ route('prestamos.historial') }}" class="text-gray-600 px-6 py-2 font-bold hover:text-cultura-600 hover:bg-gray-50 rounded-md transition">Historial (Log)</a>
            </div>

            <!-- BUSCADOR CORREGIDO -->
            <div class="mb-8 flex justify-center">
                <form action="{{ route('activos.index') }}" method="GET" class="w-full md:w-1/2 flex shadow-md rounded-md overflow-hidden">
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o código QR..."
                           class="w-full border-gray-300 bg-white text-gray-900 placeholder-gray-400 p-4 text-lg focus:ring-2 focus:ring-cultura-500 focus:outline-none">
                    <button type="submit" class="bg-cultura-600 hover:bg-cultura-700 text-white px-8 py-2 font-black transition uppercase tracking-widest">
                        Buscar
                    </button>
                </form>
            </div>

            <!-- ALERTAS DE ÉXITO Y ERROR -->
            @if (session('success'))
                <div class="mb-8 bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded-r shadow-sm">
                    <div class="flex items-center">
                        <span class="text-xl mr-3">✅</span>
                        <p class="font-bold">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-8 bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-r shadow-sm">
                    <div class="flex items-center mb-2">
                        <span class="text-xl mr-3">⚠️</span>
                        <p class="font-black uppercase tracking-widest">No se pudo procesar la solicitud</p>
                    </div>
                    <ul class="list-disc list-inside text-sm font-medium ml-8">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- LISTADO DE INSTRUMENTOS -->
            <div class="grid grid-cols-1 gap-8">
                @forelse($instrumentos as $item)
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl p-8 border-t-8 {{ $item->estado_actual == 'Disponible' ? 'border-green-500' : 'border-red-500' }} border-x border-b border-gray-200">
                        <div class="flex flex-col md:flex-row items-start md:items-center">

                            <div class="flex flex-col items-center space-y-4 md:mr-12 mb-6 md:mb-0 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <div class="bg-white p-3 rounded-xl shadow-inner border border-gray-200" id="qr-{{ $item->id_activo }}">
                                    {!! QrCode::size(140)->generate($item->codigo_qr) !!}
                                </div>
                                <button onclick="downloadQR('qr-{{ $item->id_activo }}', '{{ $item->codigo_qr }}')" class="text-sm bg-white hover:bg-gray-100 text-cultura-600 px-5 py-2 rounded-full border border-cultura-200 shadow-sm transition flex items-center font-black uppercase tracking-tighter">
                                    💾 Descargar QR
                                </button>
                            </div>

                            <div class="flex-grow space-y-2">
                                <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ $item->nombre }}</h3>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm font-mono bg-cultura-50 text-cultura-700 px-3 py-1 rounded-md font-bold border border-cultura-100 italic">ID: {{ $item->codigo_qr }}</span>
                                    <span class="text-sm text-gray-500 font-bold uppercase tracking-widest">{{ $item->categoria }}</span>
                                </div>

                                <div class="mt-6">
                                    <span class="px-6 py-2 rounded-full text-sm font-black uppercase tracking-widest shadow-sm
                                        {{ $item->estado_actual == 'Disponible' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                        {{ $item->estado_actual }}
                                    </span>
                                </div>

                                <div class="mt-4 text-gray-600 text-sm italic">
                                    Uso acumulado: <span class="font-bold text-gray-900">{{ $item->horas_uso }} horas</span>
                                </div>
                            </div>

                            <!-- FORMULARIO DE PRÉSTAMO RÁPIDO -->
                            <div class="mt-8 md:mt-0 w-full md:w-96 bg-gray-50 p-6 rounded-2xl border border-gray-200 shadow-inner">
                                @if($item->estado_actual == 'Disponible')
                                    <form action="{{ route('prestamos.store') }}" method="POST" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="id_activo" value="{{ $item->id_activo }}">

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">Nombre del Solicitante:</label>
                                            <input type="text" name="nombre_solicitante" required placeholder="Nombre completo"
                                                   class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 p-3 mt-1 focus:ring-2 focus:ring-cultura-500 focus:border-cultura-500 shadow-sm">
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-xs font-black text-gray-500 uppercase tracking-widest">Contacto / ID:</label>
                                                <input type="text" name="contacto_solicitante" required placeholder="Tel. o Matrícula"
                                                       class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 p-3 mt-1 focus:ring-2 focus:ring-cultura-500 focus:border-cultura-500 shadow-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs font-black text-gray-500 uppercase tracking-widest">Fecha Salida:</label>
                                                <p class="text-lg font-black text-gray-800 mt-2">{{ now()->format('d/m/Y') }}</p>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">Condiciones de Entrega:</label>
                                            <textarea name="condiciones_entrega" required rows="2" placeholder="Describa el estado físico actual..."
                                                      class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 p-3 mt-1 focus:ring-2 focus:ring-cultura-500 focus:border-cultura-500 shadow-sm text-sm"></textarea>
                                        </div>

                                        <div class="border-t border-gray-200 pt-3">
                                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">Devolución Prevista:</label>
                                            <input type="datetime-local" name="fecha_devolucion_prevista" required
                                                min="{{ now()->format('Y-m-d\TH:i') }}"
                                                class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 p-3 mt-1 focus:ring-2 focus:ring-cultura-500 focus:border-cultura-500 shadow-sm text-sm">
                                        </div>

                                        <button type="submit" class="w-full bg-acento-principal hover:bg-acento-hover text-white font-black py-4 rounded-xl shadow-lg transition transform active:scale-95 text-lg uppercase tracking-widest">
                                            Confirmar Préstamo
                                        </button>
                                    </form>
                                @else
                                    <div class="text-center py-10">
                                        <div class="text-5xl mb-4">⏳</div>
                                        <span class="inline-block bg-white text-red-600 font-black py-3 px-8 rounded-xl border-2 border-red-100 uppercase text-sm shadow-sm">
                                            Instrumento en Uso
                                        </span>
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
            img.onload = function() {
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
