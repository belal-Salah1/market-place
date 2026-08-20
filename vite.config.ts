import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    // Vite 7 rejects cross-origin dev-server requests by default, so assets 404 with a
    // CORS error when the app is opened through a tunnel. Allow the tunnel hosts only.
    server: {
        cors: {
            origin: [/^https:\/\/[\w-]+\.trycloudflare\.com$/, /^https:\/\/[\w-]+\.ngrok-free\.app$/, /^http:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/],
        },
        allowedHosts: ['.trycloudflare.com', '.ngrok-free.app'],
    },
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({
            formVariants: true,
        }),
    ],
});
