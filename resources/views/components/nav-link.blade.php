@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-anil-400 dark:border-anil-600 text-base font-medium leading-5 text-anil-900 dark:text-hueso-100 focus:outline-none focus:border-anil-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-base font-medium leading-5 text-cantera-600 dark:text-cantera-500 hover:text-anil-700 dark:hover:text-hueso-200 hover:border-cantera-300 dark:hover:border-gray-700 focus:outline-none focus:text-anil-700 dark:focus:text-hueso-200 focus:border-cantera-300 dark:focus:border-gray-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
