@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-base text-cantera-600 dark:text-cantera-400']) }}>
        {{ $status }}
    </div>
@endif
