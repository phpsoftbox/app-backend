import {defineConfig} from 'vite';
import react from '@vitejs/plugin-react';
import {resolve} from 'path';

export default defineConfig(({command}) => {
    const isDev = command === 'serve';

    return {
        plugins: [react()],
        root: __dirname,
        base: isDev ? '/' : '/build/',
        build: {
            outDir: 'public/build',
            emptyOutDir: true,
            manifest: true,
            rollupOptions: {
                input: 'resources/js/app.tsx',
            },
        },
        server: {
            host: true,
            port: 5173,
            strictPort: true,
            cors: true,
            headers: {
                'Access-Control-Allow-Origin': '*',
            },
            hmr: {
                host: 'vite.domain.local',
                protocol: 'wss',
                clientPort: 443,
            },
            origin: 'https://vite.domain.local',
        },

        css: {
            preprocessorOptions: {
                scss: {
                    api: 'modern-compiler',
                    quietDeps: true,
                }
            }
        },
        resolve: {
            alias: {
                '@': resolve(__dirname, 'resources/js')
            },
        }
    };
});
