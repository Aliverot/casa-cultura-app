<x-app-layout>
    <div class="py-12 bg-hueso-100 min-h-screen">
        <div class="module-page-shell">
            <x-module-nav current="devoluciones" />

            @if ($errors->any())
                <div class="mb-8 bg-oxido-50 border-l-4 border-oxido-500 text-oxido-800 p-4 rounded-r shadow-sm">
                    <p class="font-black uppercase tracking-widest mb-2">No se pudo procesar la devolución</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card">
                <h3 class="text-2xl font-black text-anil-900 mb-6">Préstamos en curso</h3>

                <div class="grid grid-cols-1 gap-6">
                    @forelse ($prestamosActivos as $item)
                        @php
                            $fechaPrevista = $item->prestamo->fecha_devolucion_prevista;
                            $estaAtrasado = now()->gt($fechaPrevista);
                        @endphp

                        <div class="border-2 border-cantera-100 rounded-2xl p-6 hover:border-anil-200 transition bg-hueso-50">
                            <div class="flex flex-col xl:flex-row gap-8">
                                <div class="flex-1 space-y-4">
                                    <div>
                                        <h4 class="text-2xl font-black text-anil-700">{{ $item->activo->nombre }}</h4>
                                        <p class="text-sm text-cantera-700 font-bold uppercase tracking-tighter">
                                            Responsable:
                                            <span class="text-anil-900">{{ $item->prestamo->nombre_solicitante }}</span>
                                        </p>
                                        <p class="text-xs text-cantera-600">Teléfono: {{ $item->prestamo->contacto_solicitante }}</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-hueso-50 p-4 rounded-xl border border-cantera-200">
                                            <p class="text-xs font-black text-cantera-500 uppercase">Salida registrada</p>
                                            <p class="text-lg font-black text-anil-800 mt-1">{{ $item->prestamo->fecha_salida->format('d/m/Y H:i') }}</p>
                                        </div>

                                        <div class="bg-hueso-50 p-4 rounded-xl border border-cantera-200">
                                            <p class="text-xs font-black text-cantera-500 uppercase">Devolución prevista</p>
                                            <p class="text-lg font-black text-anil-800 mt-1">{{ $fechaPrevista->format('d/m/Y H:i') }}</p>
                                        </div>

                                        <div class="bg-hueso-50 p-4 rounded-xl border border-cantera-200">
                                            <p class="text-xs font-black text-cantera-500 uppercase">Comparación actual</p>
                                            <span class="inline-flex mt-2 px-3 py-1 rounded-full text-xs font-black uppercase {{ $estaAtrasado ? 'bg-oxido-100 text-oxido-700' : 'bg-cantera-100 text-cantera-700' }}">
                                                {{ $estaAtrasado ? 'Fuera de tiempo' : 'En tiempo y forma' }}
                                            </span>
                                            <p class="text-xs text-cantera-600 mt-2">La entrega real se registra al confirmar la devolución.</p>
                                        </div>
                                    </div>

                                    <div class="bg-hueso-50 p-4 rounded-xl border border-cantera-200">
                                        <p class="text-xs font-black text-cantera-500 uppercase">Condiciones de salida</p>
                                        <p class="text-sm italic text-anil-700 mt-2">{{ $item->prestamo->condiciones_entrega }}</p>
                                    </div>
                                </div>

                                <div class="xl:w-[26rem]">
                                    <form action="{{ route('prestamos.devolver', $item->id_detalle) }}" method="POST" class="space-y-4 bg-hueso-50 rounded-2xl border border-cantera-200 p-5 shadow-sm">
                                        @csrf

                                        <div>
                                            <label class="text-xs font-black text-cantera-600 uppercase">Estado del equipo al volver</label>
                                            <select name="estado_equipo" class="w-full rounded-lg border-cantera-300 text-sm p-3 mt-1 bg-hueso-50 text-anil-900 focus:ring-2 focus:ring-anil-500" data-damage-toggle>
                                                <option value="Buen estado">Buen estado</option>
                                                <option value="Danado">Dañado</option>
                                                <option value="Extraviado">Extraviado</option>
                                                <option value="Perdida total">Pérdida total</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-cantera-600 uppercase">Condiciones de retorno</label>
                                            <textarea
                                                name="condiciones_devolucion"
                                                required
                                                rows="3"
                                                class="w-full rounded-lg border-cantera-300 text-sm p-3 mt-1 bg-hueso-50 text-anil-900 focus:ring-2 focus:ring-anil-500"
                                                placeholder="Ej. Regresa limpio, con desgaste normal, o describe el daño encontrado."
                                            ></textarea>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-cantera-600 uppercase">Contexto del incidente</label>
                                            <textarea
                                                name="contexto_incidente"
                                                rows="2"
                                                class="w-full rounded-lg border-cantera-300 text-sm p-3 mt-1 bg-hueso-50 text-anil-900 focus:ring-2 focus:ring-anil-500"
                                                placeholder="Cómo ocurrió el daño"
                                                data-damage-detail
                                            >{{ old('contexto_incidente') }}</textarea>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-cantera-600 uppercase">Entorno de uso</label>
                                            <select name="entorno_uso" class="w-full rounded-lg border-cantera-300 text-sm p-3 mt-1 bg-hueso-50 text-anil-900 focus:ring-2 focus:ring-anil-500" data-damage-detail>
                                                <option value="">Seleccionar</option>
                                                <option value="Ensayo" @selected(old('entorno_uso') === 'Ensayo')>Ensayo</option>
                                                <option value="Evento exterior" @selected(old('entorno_uso') === 'Evento exterior')>Evento exterior</option>
                                                <option value="Transporte" @selected(old('entorno_uso') === 'Transporte')>Transporte</option>
                                                <option value="Otro" @selected(old('entorno_uso') === 'Otro')>Otro</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-cantera-600 uppercase">Accesorios de protección</label>
                                            <textarea
                                                name="accesorios_proteccion"
                                                rows="2"
                                                class="w-full rounded-lg border-cantera-300 text-sm p-3 mt-1 bg-hueso-50 text-anil-900 focus:ring-2 focus:ring-anil-500"
                                                placeholder="Estuches, fundas o protecciones"
                                                data-damage-detail
                                            >{{ old('accesorios_proteccion') }}</textarea>
                                        </div>

                                        <button type="submit" class="btn-primary-wide py-3 text-xs">
                                            Procesar devolución
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-cantera-500 py-10 font-medium">No hay instrumentos fuera de la institución actualmente.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-damage-toggle]').forEach((select) => {
            const form = select.closest('form');
            const damageDetails = form.querySelectorAll('[data-damage-detail]');

            const syncDamageFields = () => {
                const requiresDamageDetails = select.value === 'Danado';

                damageDetails.forEach((field) => {
                    field.disabled = !requiresDamageDetails;

                    if (!requiresDamageDetails) {
                        field.value = '';
                    }
                });
            };

            syncDamageFields();
            select.addEventListener('change', syncDamageFields);
        });
    </script>
</x-app-layout>
