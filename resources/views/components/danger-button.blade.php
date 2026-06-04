<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-oxido-600 border border-transparent rounded-md font-semibold text-sm text-hueso-50 uppercase tracking-widest hover:bg-oxido-500 active:bg-oxido-700 focus:outline-none focus:ring-2 focus:ring-oxido-500 focus:ring-offset-2 dark:focus:ring-offset-anil-900 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
