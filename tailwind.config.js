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
                sans: ['Plus Jakarta Sans', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#F0FBFB',
                    100: '#D6F4F6',
                    200: '#B0E9ED',
                    300: '#7CD8DF',
                    400: '#41C1CB',
                    500: '#199CA4',
                    600: '#15838B',
                    700: '#146970',
                    800: '#15555B',
                    900: '#16484D',
                },
            },
            boxShadow: {
                'card': '0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.03)',
                'card-hover': '0 12px 28px -6px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(25, 156, 164, 0.06)',
                'teal-glow': '0 4px 20px -2px rgba(25, 156, 164, 0.3)',
            },
        },
    },

    plugins: [forms],
};
