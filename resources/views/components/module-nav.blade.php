@props(['current' => null])

@php
    $items = [
        [
            'key' => 'catalogo',
            'label' => 'Catálogo',
            'route' => route('activos.index'),
            'idle' => 'border-blue-200 text-blue-900 hover:border-blue-400 hover:bg-blue-50',
            'active' => 'border-blue-500 bg-blue-50 text-blue-900 shadow-sm shadow-blue-100',
        ],
        [
            'key' => 'instrumentos',
            'label' => 'Instrumentos nuevos',
            'route' => route('activos.create'),
            'idle' => 'border-sky-200 text-sky-900 hover:border-sky-400 hover:bg-sky-50',
            'active' => 'border-sky-500 bg-sky-50 text-sky-900 shadow-sm shadow-sky-100',
        ],
        [
            'key' => 'prestamos',
            'label' => 'Nuevo préstamo',
            'route' => route('prestamos.create'),
            'idle' => 'border-green-200 text-green-900 hover:border-green-400 hover:bg-green-50',
            'active' => 'border-green-500 bg-green-50 text-green-900 shadow-sm shadow-green-100',
        ],
        [
            'key' => 'devoluciones',
            'label' => 'Devoluciones / multas',
            'route' => route('prestamos.activos'),
            'idle' => 'border-purple-200 text-purple-900 hover:border-purple-400 hover:bg-purple-50',
            'active' => 'border-purple-500 bg-purple-50 text-purple-900 shadow-sm shadow-purple-100',
        ],
        [
            'key' => 'mantenimiento',
            'label' => 'Mantenimiento',
            'route' => route('mantenimientos.index'),
            'idle' => 'border-amber-200 text-amber-900 hover:border-amber-400 hover:bg-amber-50',
            'active' => 'border-amber-500 bg-amber-50 text-amber-900 shadow-sm shadow-amber-100',
        ],
        [
            'key' => 'temporadas',
            'label' => 'Temporadas',
            'route' => route('temporadas-base.index'),
            'idle' => 'border-blue-200 text-blue-900 hover:border-blue-400 hover:bg-blue-50',
            'active' => 'border-blue-500 bg-blue-50 text-blue-900 shadow-sm shadow-blue-100',
        ],
        [
            'key' => 'historial',
            'label' => 'Histórico de préstamos',
            'route' => route('prestamos.historial'),
            'idle' => 'border-slate-200 text-slate-800 hover:border-slate-400 hover:bg-slate-50',
            'active' => 'border-slate-500 bg-slate-50 text-slate-900 shadow-sm shadow-slate-100',
        ],
    ];
@endphp

<div class="module-nav-shell">
    <nav class="module-nav-list" aria-label="Módulos del sistema">
        @foreach ($items as $item)
            <a
                href="{{ $item['route'] }}"
                class="module-nav-link {{ $current === $item['key'] ? $item['active'] : $item['idle'] }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</div>
