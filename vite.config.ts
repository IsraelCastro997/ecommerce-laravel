import react from '@vitejs/plugin-react';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import path from 'node:path';
import tailwindcss from 'tailwindcss';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            refresh: true,
        }),
        react(),
    ],
    resolve: {
        alias: { '@': path.resolve(__dirname, './resources/js') },
    },
    css: {
        postcss: { plugins: [tailwindcss, autoprefixer] },
    },
});
