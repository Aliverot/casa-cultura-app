import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // 1. CAMBIO DE COLOR DE LA INTERFAZ
            // Aquí definimos los colores institucionales para usarlos en todo el sistema
            colors: {
                cultura: {
                    50: '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6', // Color principal (puedes cambiar este código HEX)
                    600: '#7c3aed', // Hover de botones
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                },
                acento: {
                    principal: '#f59e0b', // Un ámbar/dorado para botones de acción como "Préstamo"
                    hover: '#d97706',
                }
            },
        },
    },

    plugins: [forms],
};
