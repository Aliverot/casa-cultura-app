<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-danger bg-oxido-600 text-hueso-50 hover:bg-oxido-700 hover:text-hueso-50']) }}>
    {{ $slot }}
</button>
