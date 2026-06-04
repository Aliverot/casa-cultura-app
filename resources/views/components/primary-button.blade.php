<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-anil-700 dark:bg-hueso-100 border border-transparent rounded-md font-semibold text-sm text-hueso-50 dark:text-anil-900 uppercase tracking-widest hover:bg-anil-800 dark:hover:bg-hueso-50 focus:bg-anil-800 dark:focus:bg-hueso-50 active:bg-anil-900 dark:active:bg-hueso-200 focus:outline-none focus:ring-2 focus:ring-ocre-400 focus:ring-offset-2 dark:focus:ring-offset-anil-900 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
