@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-cantera-300 bg-hueso-50 text-anil-900 dark:border-anil-700 dark:bg-anil-900 dark:text-hueso-100 focus:border-anil-600 dark:focus:border-ocre-400 focus:ring-ocre-400 dark:focus:ring-ocre-400 rounded-md shadow-sm']) }}>
