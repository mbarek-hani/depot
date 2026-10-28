import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import inertia from '@inertiajs/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Cairo', {
                    weights: [400, 500, 600, 700, 800],
                    subsets: ['arabic', 'latin'],
                }),
                bunny('Tajawal', {
                    weights: [500, 700, 800],
                    subsets: ['arabic', 'latin'],
                }),
            ],
        }),
        inertia({ ssr: false }),
        vue(),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
