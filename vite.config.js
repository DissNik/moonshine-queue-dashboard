import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/stylesheet.css', 'resources/js/script.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        emptyOutDir: true,
        outDir: 'public',
        rollupOptions: {
            output: {
                assetFileNames: '[name][extname]',
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
            }
        },
    },
});
