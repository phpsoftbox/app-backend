import {defineConfig} from 'vite';
import react from '@vitejs/plugin-react';
import {resolve} from 'path';

const devServerUrl = () => {
    const value = process.env.VITE_DEV_SERVER || 'https://vite.domain.local';

    return new URL(value);
};

export default defineConfig(({command}) => {
    const isDev = command === 'serve';
    const devUrl = devServerUrl();
    const devPort = Number(process.env.VITE_PORT || devUrl.port || 5173);
    const isSecure = devUrl.protocol === 'https:';

    return {
        plugins: [react()],
        root: __dirname,
        base: isDev ? '/' : '/build/',
        publicDir: false,
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
            port: devPort,
            strictPort: true,
            cors: true,
            headers: {
                'Access-Control-Allow-Origin': '*',
            },
            hmr: {
                host: devUrl.hostname,
                protocol: isSecure ? 'wss' : 'ws',
                clientPort: Number(devUrl.port || (isSecure ? 443 : 80)),
            },
            origin: devUrl.origin,
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
