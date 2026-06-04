@props(['current' => null, 'title' => null])

@php
    $items = [
        [
            'key' => 'catalogo',
            'label' => 'Catálogo',
            'route' => route('activos.index'),
        ],
        [
            'key' => 'instrumentos',
            'label' => 'Nuevo activo',
            'route' => route('activos.create'),
        ],
        [
            'key' => 'prestamos',
            'label' => 'Préstamo',
            'route' => route('prestamos.create'),
        ],
        [
            'key' => 'devoluciones',
            'label' => 'Devoluciones',
            'route' => route('prestamos.activos'),
        ],
        [
            'key' => 'mantenimiento',
            'label' => 'Mantenimiento',
            'route' => route('mantenimientos.index'),
        ],
        [
            'key' => 'temporadas',
            'label' => 'Temporadas',
            'route' => route('temporadas-base.index'),
        ],
        [
            'key' => 'historial',
            'label' => 'Histórico',
            'route' => route('prestamos.historial'),
        ],
    ];

    $activeItem = collect($items)->firstWhere('key', $current);
    $pageTitle = $title ?? match ($current) {
        'catalogo' => 'Gestión de inventario',
        'instrumentos' => 'Registro de nuevo activo',
        'prestamos' => 'Nuevo préstamo',
        'devoluciones' => 'Centro de devoluciones',
        'mantenimiento' => 'Mantenimiento preventivo',
        'temporadas' => 'Temporadas culturales',
        'historial' => 'Histórico de préstamos',
        default => 'Panel de control',
    };
@endphp

<div class="module-nav-shell">
    <div class="module-nav-heading">
        <span class="module-nav-title">{{ $pageTitle }}</span>
    </div>

    <nav class="module-nav-list" aria-label="Navegación principal del sistema">
        @foreach ($items as $item)
            <a
                href="{{ $item['route'] }}"
                class="module-nav-link {{ $current === $item['key'] ? 'module-nav-link-active' : 'module-nav-link-idle' }}"
                @if ($current === $item['key']) aria-current="page" @endif
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</div>
