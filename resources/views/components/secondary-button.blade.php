<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-hueso-50 dark:bg-anil-900 border border-cantera-300 dark:border-cantera-500 rounded-md font-semibold text-sm text-anil-700 dark:text-hueso-200 uppercase tracking-widest shadow-sm hover:bg-hueso-50 dark:hover:bg-anil-800 focus:outline-none focus:ring-2 focus:ring-anil-500 focus:ring-offset-2 dark:focus:ring-offset-anil-900 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
