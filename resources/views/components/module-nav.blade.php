@props(['current' => null])

@php
    $items = [
        ['key' => 'catalogo', 'label' => 'Catalogo', 'route' => route('activos.index')],
        ['key' => 'instrumentos', 'label' => 'Instrumentos Nuevos', 'route' => route('activos.create')],
        ['key' => 'prestamos', 'label' => 'Nuevo Prestamo', 'route' => route('prestamos.create')],
        ['key' => 'devoluciones', 'label' => 'Devoluciones / Multas', 'route' => route('prestamos.activos')],
        ['key' => 'mantenimiento', 'label' => 'Mantenimiento', 'route' => route('mantenimientos.index')],
        ['key' => 'temporadas', 'label' => 'Temporadas', 'route' => route('temporadas-base.index')],
        ['key' => 'historial', 'label' => 'Historial', 'route' => route('prestamos.historial')],
    ];
@endphp

<div class="module-nav-shell">
    <nav class="module-nav-list" aria-label="Modulos del sistema">
        @foreach ($items as $item)
            <a
                href="{{ $item['route'] }}"
                class="module-nav-link {{ $current === $item['key'] ? 'module-nav-link-active' : '' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</div>
