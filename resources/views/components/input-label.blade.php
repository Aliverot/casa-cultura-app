@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-base text-anil-900 dark:text-hueso-100']) }}>
    {{ $value ?? $slot }}
</label>
