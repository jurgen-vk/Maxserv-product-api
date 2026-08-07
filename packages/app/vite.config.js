import { defineConfig, loadEnv } from 'vite';
import { cssComponentGlob } from './vite-plugins/css-component-glob.js';
import { fullReload } from './vite-plugins/full-reload.js';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import path from 'node:path';

export default defineConfig(({mode}) => {
  const envDir = path.resolve(import.meta.dirname, '../..');
  const env = loadEnv(mode, envDir, '');

  return {
    plugins: [
      cssComponentGlob(import.meta.dirname),
      fullReload(import.meta.dirname, ['templates/**/*.html.twig']),
      viteStaticCopy({
        silent: true,
        structured: true,
        targets: [
          { src: '../core/assets/site/*', dest: '.', rename: { stripBase: 2 } },
          { src: 'assets/site/*', dest: '.', rename: { stripBase: 1 } },
          { src: '../core/assets/icons/**/*', dest: 'icons', rename: { stripBase: 3 } },
          { src: 'assets/icons/**/*', dest: 'icons', rename: { stripBase: 2 } },
        ],
      }),
    ],
    base: '/assets/',
    publicDir: false,
    envDir,
    resolve: {
      alias: {
        '@app': path.resolve(import.meta.dirname, 'assets/js'),
        '@core': path.resolve(import.meta.dirname, '../core/assets/js')
      }
    },
    server: {
      cors: {origin: env.APP_URL}
    },
    build: {
      outDir: '../../public/assets',
      assetsDir: 'build',
      emptyOutDir: true,
      manifest: true,
      rolldownOptions: {
        input: ['assets/js/app.js', 'assets/css/app.css']
      }
    }
  };
});
