import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fg from 'fast-glob';

const logoImages = fg.sync('resources/images/**/*.png');

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                ...logoImages,
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            protocol: 'ws',
            clientPort: 5173,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        sourcemap: true,
        rollupOptions: {
            onwarn(warning, warn) {
                if (warning.code === 'SOURCEMAP_ERROR') {
                    return;
                }
                warn(warning);
            },
        },
    },
    logLevel: 'warn',
});
