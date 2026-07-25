import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            outDir: 'public',
            buildBase: '/',
            scope: '/',
            injectRegister: 'auto',
            // The manifest is generated per tenant by the `tenant.manifest` route
            // (name, theme colour and icons come from that tenant's branding), and
            // linked from app.blade.php. Emitting a second static manifest here
            // would compete with that link, so only the service worker is built.
            manifest: false,
        })
    ],
});
