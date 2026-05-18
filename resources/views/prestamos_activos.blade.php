<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Centro de Devoluciones y Pagos') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="module-page-shell">
            <x-module-nav current="devoluciones" />

            @if ($errors->any())
                <div class="mb-8 bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-r shadow-sm">
                    <p class="font-black uppercase tracking-widest mb-2">No se pudo procesar la devolucion</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card mb-10">
                <h3 class="text-2xl font-black text-gray-900 mb-6">Prestamos en curso</h3>

                <div class="grid grid-cols-1 gap-6">
                    @forelse ($prestamosActivos as $item)
                        @php
                            $fechaPrevista = $item->prestamo->fecha_devolucion_prevista;
                            $estaAtrasado = now()->gt($fechaPrevista);
                        @endphp

                        <div class="border-2 border-gray-100 rounded-2xl p-6 hover:border-blue-200 transition bg-gray-50">
                            <div class="flex flex-col xl:flex-row gap-8">
                                <div class="flex-1 space-y-4">
                                    <div>
                                        <h4 class="text-2xl font-black text-blue-700">{{ $item->activo->nombre }}</h4>
                                        <p class="text-sm text-gray-600 font-bold uppercase tracking-tighter">
                                            Responsable:
                                            <span class="text-gray-900">{{ $item->prestamo->nombre_solicitante }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500">Contacto: {{ $item->prestamo->contacto_solicitante }}</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-white p-4 rounded-xl border border-gray-200">
                                            <p class="text-xs font-black text-gray-400 uppercase">Salida registrada</p>
                                            <p class="text-lg font-black text-gray-800 mt-1">{{ $item->prestamo->fecha_salida->format('d/m/Y H:i') }}</p>
                                        </div>

                                        <div class="bg-white p-4 rounded-xl border border-gray-200">
                                            <p class="text-xs font-black text-gray-400 uppercase">Devolucion prevista</p>
                                            <p class="text-lg font-black text-gray-800 mt-1">{{ $fechaPrevista->format('d/m/Y H:i') }}</p>
                                        </div>

                                        <div class="bg-white p-4 rounded-xl border border-gray-200">
                                            <p class="text-xs font-black text-gray-400 uppercase">Comparacion actual</p>
                                            <span class="inline-flex mt-2 px-3 py-1 rounded-full text-xs font-black uppercase {{ $estaAtrasado ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                                {{ $estaAtrasado ? 'Fuera de tiempo' : 'En tiempo y forma' }}
                                            </span>
                                            <p class="text-xs text-gray-500 mt-2">La entrega real se registra al confirmar la devolucion.</p>
                                        </div>
                                    </div>

                                    <div class="bg-white p-4 rounded-xl border border-gray-200">
                                        <p class="text-xs font-black text-gray-400 uppercase">Condiciones de salida</p>
                                        <p class="text-sm italic text-gray-700 mt-2">{{ $item->prestamo->condiciones_entrega }}</p>
                                    </div>
                                </div>

                                <div class="xl:w-[26rem]">
                                    <form action="{{ route('prestamos.devolver', $item->id_detalle) }}" method="POST" class="space-y-4 bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                                        @csrf

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase">Estado del equipo al volver</label>
                                            <select name="estado_equipo" class="w-full rounded-lg border-gray-300 text-sm p-3 mt-1 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500" data-damage-toggle>
                                                <option value="Buen estado">Buen estado</option>
                                                <option value="Danado">Da&ntilde;ado</option>
                                                <option value="Extraviado">Extraviado</option>
                                                <option value="Perdida total">P&eacute;rdida total</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase">Condiciones de retorno</label>
                                            <textarea
                                                name="condiciones_devolucion"
                                                required
                                                rows="3"
                                                class="w-full rounded-lg border-gray-300 text-sm p-3 mt-1 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500"
                                                placeholder="Ej. Regresa limpio, con desgaste normal, o describe el dano encontrado."
                                            ></textarea>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase">Contexto del incidente</label>
                                            <textarea
                                                name="contexto_incidente"
                                                rows="2"
                                                class="w-full rounded-lg border-gray-300 text-sm p-3 mt-1 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500"
                                                placeholder="Como ocurrio el dano"
                                                data-damage-detail
                                            >{{ old('contexto_incidente') }}</textarea>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase">Entorno de uso</label>
                                            <select name="entorno_uso" class="w-full rounded-lg border-gray-300 text-sm p-3 mt-1 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500" data-damage-detail>
                                                <option value="">Seleccionar</option>
                                                <option value="Ensayo" @selected(old('entorno_uso') === 'Ensayo')>Ensayo</option>
                                                <option value="Evento exterior" @selected(old('entorno_uso') === 'Evento exterior')>Evento exterior</option>
                                                <option value="Transporte" @selected(old('entorno_uso') === 'Transporte')>Transporte</option>
                                                <option value="Otro" @selected(old('entorno_uso') === 'Otro')>Otro</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase">Accesorios de proteccion</label>
                                            <textarea
                                                name="accesorios_proteccion"
                                                rows="2"
                                                class="w-full rounded-lg border-gray-300 text-sm p-3 mt-1 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500"
                                                placeholder="Estuches, fundas o protecciones"
                                                data-damage-detail
                                            >{{ old('accesorios_proteccion') }}</textarea>
                                        </div>

                                        <div>
                                            <label class="text-xs font-black text-gray-500 uppercase">Costo de reparacion o reposicion</label>
                                            <div class="relative mt-1">
                                                <span class="absolute left-3 top-3 text-gray-400">$</span>
                                                <input
                                                    type="number"
                                                    name="costo_reparacion"
                                                    value="0"
                                                    min="0"
                                                    step="0.01"
                                                    class="w-full rounded-lg border-gray-300 pl-7 p-3 text-sm bg-white text-gray-900 focus:ring-2 focus:ring-blue-500"
                                                    data-damage-cost
                                                    disabled
                                                >
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">Se habilita para da&ntilde;ado, extraviado o p&eacute;rdida total.</p>
                                        </div>

                                        <button type="submit" class="w-full bg-cultura-600 hover:bg-cultura-700 text-white font-black py-3 rounded-xl shadow-md uppercase text-xs tracking-widest">
                                            Procesar devolucion
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-400 py-10 font-medium">No hay instrumentos fuera de la institucion actualmente.</p>
                    @endforelse
                </div>
            </div>

            <div class="module-card">
                <h3 class="text-2xl font-black text-gray-900 mb-6">Cobros pendientes por reparacion</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b-2 border-gray-100 text-gray-400 text-xs uppercase tracking-widest">
                                <th class="pb-4">Instrumento</th>
                                <th class="pb-4">Responsable</th>
                                <th class="pb-4">Entrega registrada</th>
                                <th class="pb-4">Monto</th>
                                <th class="pb-4 text-right">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($multasPendientes as $multa)
                                @php
                                    $estadoRetorno = in_array($multa->estado_retorno, ['Danado', 'Dañado', 'DaÃ±ado'], true)
                                        ? 'Da&ntilde;ado'
                                        : ($multa->estado_retorno === 'Perdida total' ? 'P&eacute;rdida total' : $multa->estado_retorno);
                                @endphp
                                <tr>
                                    <td class="py-4 font-bold text-gray-900">{{ $multa->activo->nombre }}</td>
                                    <td class="py-4 text-sm text-gray-600">{{ $multa->prestamo->nombre_solicitante }}</td>
                                    <td class="py-4 text-sm text-gray-600">
                                        <p class="font-semibold">{!! $estadoRetorno !!}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $multa->prestamo->condiciones_devolucion }}</p>
                                    </td>
                                    <td class="py-4">
                                        <span class="text-red-600 font-black text-lg">${{ number_format((float) $multa->prestamo->costo_reparacion, 2) }}</span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <form action="{{ route('prestamos.liquidar', $multa->prestamo->id_prestamo) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-cultura-100 px-4 py-2 text-xs font-black uppercase text-cultura-700 transition hover:bg-cultura-600 hover:text-white">
                                                Marcar pagado
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-gray-400 font-medium">No hay cobros pendientes de reparaciones.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-damage-toggle]').forEach((select) => {
            const form = select.closest('form');
            const costInput = form.querySelector('[data-damage-cost]');
            const damageDetails = form.querySelectorAll('[data-damage-detail]');

            const syncCostField = () => {
                const requiresCost = ['Danado', 'Extraviado', 'Perdida total'].includes(select.value);
                const requiresDamageDetails = select.value === 'Danado';
                costInput.disabled = !requiresCost;
                damageDetails.forEach((field) => {
                    field.disabled = !requiresDamageDetails;

                    if (!requiresDamageDetails) {
                        field.value = '';
                    }
                });

                if (!requiresCost) {
                    costInput.value = '0';
                }
            };

            syncCostField();
            select.addEventListener('change', syncCostField);
        });
    </script>
</x-app-layout>
