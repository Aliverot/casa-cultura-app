<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Inventario - Casa de la Cultura') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-gray-200 dark:bg-gray-700 p-4 rounded-lg flex justify-center space-x-8 mb-6 shadow-sm">
                <a href="{{ route('catalogo') }}" class="bg-gray-800 dark:bg-gray-900 text-white px-4 py-1 rounded-md shadow">Catálogo</a>
                <a href="{{ route('activos.create') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Instrumentos Nuevos</a>
                <a href="{{ route('prestamos.activos') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Préstamos</a>
                <a href="{{ route('mantenimiento.index') }}" class="text-gray-700 dark:text-gray-200 font-bold hover:text-blue-600">Mantenimiento</a>
            </div>

            <div class="mb-8 flex justify-center">
                <form action="{{ route('catalogo') }}" method="GET" class="w-full md:w-1/2 flex shadow-lg">
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o código QR..." 
                           class="w-full border-gray-300 dark:border-gray-500 dark:bg-gray-700 dark:text-white placeholder-gray-400 rounded-l-md focus:ring-blue-500 focus:border-blue-500 p-3 text-lg">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-r-md font-bold transition text-lg shadow-md">
                        Buscar
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 gap-6">
                @forelse($instrumentos as $item)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl p-6 border-l-8 {{ $item->estado_actual == 'Disponible' ? 'border-green-500' : 'border-red-500' }}">
                        <div class="flex flex-col md:flex-row items-center">
                            
                            <div class="flex flex-col items-center space-y-3 md:mr-10 mb-4 md:mb-0">
                                <div class="bg-white p-3 rounded-lg shadow-md border border-gray-100" id="qr-{{ $item->id_activo }}">
                                    {!! QrCode::size(120)->generate($item->codigo_qr) !!}
                                </div>
                                <button onclick="downloadQR('qr-{{ $item->id_activo }}', '{{ $item->codigo_qr }}')" class="text-xs bg-blue-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-blue-700 dark:text-gray-200 px-4 py-1.5 rounded-full border border-blue-200 dark:border-gray-500 transition flex items-center font-bold">
                                    💾 Descargar QR
                                </button>
                            </div>

                            <div class="flex-grow text-center md:text-left space-y-1">
                                <h3 class="text-2xl font-black text-gray-500 dark:text-gray-300 tracking-tight">{{ $item->nombre }}</h3>
                                <p class="text-sm font-mono text-gray-500 dark:text-gray-300 font-medium">ID: {{ $item->codigo_qr }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-300 font-medium">Categoría: {{ $item->categoria }}</p>
                                <div class="mt-4">
                                    <span class="px-4 py-1.5 rounded-full text-sm font-black uppercase tracking-widest {{ $item->estado_actual == 'Disponible' ? 'bg-green-500 text-white dark:bg-green-600' : 'bg-red-500 text-white dark:bg-red-600' }} shadow-sm">
                                        {{ $item->estado_actual }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-6 md:mt-0 w-full md:w-auto bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border dark:border-gray-700">
                                @if($item->estado_actual == 'Disponible')
                                    <form action="{{ route('prestamos.store') }}" method="POST" class="flex flex-col space-y-3">
                                        @csrf 
                                        <input type="hidden" name="id_activo" value="{{ $item->id_activo }}">
                                        <input type="hidden" name="id_usuario" value="{{ auth()->user()->id_usuario }}">
                                        
                                        <label class="text-xs font-black text-gray-600 dark:text-gray-300 uppercase tracking-widest">Fecha de Retorno:</label>
                                        <input type="datetime-local" name="fecha_devolucion_prevista" required 
                                               class="border-gray-300 dark:border-gray-500 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-lg p-3 focus:ring-blue-500 focus:border-blue-500">
                                        
                                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3 px-6 rounded-lg shadow-lg transition transform active:scale-95 text-lg">
                                            PRESTAR AHORA
                                        </button>
                                    </form>
                                @else
                                    <div class="text-center py-4">
                                        <span class="inline-block bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-black py-3 px-8 rounded-lg border dark:border-gray-600 uppercase text-sm">
                                            ⚠️ EN USO
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400 text-lg">No hay resultados.</p>
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