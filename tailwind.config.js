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
                "display": ["Manrope", "sans-serif"],
                "body": ["Manrope", "sans-serif"],
            },
            colors: {
                "primary": "#f48c25",
                "primary-hover": "#e07b1a",
                "background-light": "#f8f7f5",
                "background-dark": "#221910", // Updated from design
                "surface-dark": "#2e241b", // Updated from design
                "surface-highlight": "#3a2e24", // New from design
                "surface-card": "#261e17",
                "text-secondary": "#baab9c",
                "text-light": "#e8e6e3",
                "text-muted": "#baab9c", // Alias for secondary
                "input-dark": "#27211b", // From login design
                "border-dark": "#54473b", // From login design
            },
            borderRadius: {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px"
            },
            backgroundImage: {
                'hero-pattern': "radial-gradient(circle at 50% 0%, rgba(244, 140, 37, 0.15) 0%, rgba(15, 12, 8, 0) 70%)",
            },
        },
    },

    plugins: [forms],
};
