@php
    $defaultSize = $attributes->get('class') ? '' : 'h-20 w-20';
@endphp

<img
    src="{{ asset('img/logo.png') }}"
    {{ $attributes->merge(['class' => trim($defaultSize . ' rounded-full shadow-md')]) }}
    alt="Logo Casa de la Cultura"
>
