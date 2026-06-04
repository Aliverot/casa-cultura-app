@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-base text-oxido-600 dark:text-oxido-300 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
