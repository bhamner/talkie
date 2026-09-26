import vue from '@vitejs/plugin-vue';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from 'tailwindcss';
import { defineConfig, type Plugin } from 'vite';
import { patchPiperPhonemizerSource } from './resources/js/lib/piperPhonemizerPatch';

function piperPhonemizerPlugin(): Plugin {
    return {
        name: 'talkie-piper-phonemizer',
        enforce: 'pre',
        transform(code, id) {
            const file = id.split('?')[0];

            if (!file.includes('/@mintplex-labs/piper-tts-web/') || !file.endsWith('/piper-tts-web.js')) {
                return null;
            }

            return patchPiperPhonemizerSource(code);
        },
    };
}

export default defineConfig({
    plugins: [
        piperPhonemizerPlugin(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
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
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            '@talkie/sherpa-tts': path.resolve(__dirname, './plugins/sherpa-tts/src/index.ts'),
        },
    },
    optimizeDeps: {
        exclude: ['onnxruntime-web', '@mintplex-labs/piper-tts-web'],
    },
    css: {
        postcss: {
            plugins: [tailwindcss, autoprefixer],
        },
    },
});
