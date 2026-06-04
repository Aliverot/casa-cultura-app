<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-cancel disabled:opacity-25']) }}>
    {{ $slot }}
</button>
