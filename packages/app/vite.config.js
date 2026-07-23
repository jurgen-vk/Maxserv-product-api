import { defineConfig, loadEnv } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import { cssComponentGlob } from './vite-plugins/css-component-glob.js'
import path from 'node:path'

export default defineConfig(({ mode }) => {
  const envDir = path.resolve(import.meta.dirname, '../..')
  const env = loadEnv(mode, envDir, '')

  return {
    plugins: [cssComponentGlob(import.meta.dirname), tailwindcss()],
    base: '/assets/',
    publicDir: false,
    envDir,
    resolve: {
      alias: { '@': path.resolve(import.meta.dirname, 'assets/js') },
    },
    server: {
      cors: { origin: env.APP_URL },
    },
    build: {
      outDir: '../../public/assets',
      emptyOutDir: true,
      manifest: true,
      rolldownOptions: {
        input: ['assets/js/app.js', 'assets/css/app.css'],
      },
    },
  }
})
