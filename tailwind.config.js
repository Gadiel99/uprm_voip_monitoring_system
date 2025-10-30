// Configuración de Tailwind CSS para el proyecto.
// - "content": rutas donde Tailwind escanea clases CSS en Blade/PHP para generar utilidades.
// - "theme.extend": personalizaciones (fuente base Figtree).
// - "plugins": activa el plugin de formularios para estilos de inputs.
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
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
        },
    },

    plugins: [forms],
};
