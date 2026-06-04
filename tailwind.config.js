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
            colors: {
                cantera: {
                    50: '#eef3ee',
                    100: '#dce7dc',
                    200: '#bbcfba',
                    300: '#92ad91',
                    400: '#6f8d6e',
                    500: '#557354',
                    600: '#415d42',
                    700: '#334a35',
                    800: '#293b2b',
                    900: '#213125',
                },
                anil: {
                    50: '#edf5fa',
                    100: '#d5e8f2',
                    200: '#afd2e4',
                    300: '#7eb2cf',
                    400: '#4d8bab',
                    500: '#2f6d8d',
                    600: '#245672',
                    700: '#1b4158',
                    800: '#15364a',
                    900: '#102a3a',
                },
                oxido: {
                    50: '#fbf0ec',
                    100: '#f5d9d0',
                    200: '#ebb4a4',
                    300: '#dc8874',
                    400: '#c9654d',
                    500: '#a94732',
                    600: '#8e3727',
                    700: '#732d23',
                    800: '#5f271f',
                    900: '#4f231d',
                },
                ocre: {
                    50: '#fcf7e8',
                    100: '#f8ebbf',
                    200: '#efd77c',
                    300: '#e5bc3d',
                    400: '#d59f24',
                    500: '#b97f18',
                    600: '#935f15',
                    700: '#744816',
                    800: '#613b18',
                    900: '#533219',
                },
                hueso: {
                    50: '#fffdf7',
                    100: '#f7f0e3',
                    200: '#eadcc6',
                    300: '#ddc5a2',
                    400: '#caa878',
                    500: '#b88d59',
                    600: '#9d7044',
                    700: '#82583a',
                    800: '#6c4934',
                    900: '#5a3d2d',
                },
                cultura: {
                    50: '#edf5fa',
                    100: '#d5e8f2',
                    200: '#afd2e4',
                    300: '#7eb2cf',
                    400: '#4d8bab',
                    500: '#2f6d8d',
                    600: '#245672',
                    700: '#1b4158',
                    800: '#15364a',
                    900: '#102a3a',
                },
                acento: {
                    principal: '#d59f24',
                    hover: '#b97f18',
                },
            },
        },
    },

    plugins: [forms],
};
