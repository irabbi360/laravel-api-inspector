import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'node:path'

export default defineConfig({
  plugins: [
    vue(),
  ],
  publicDir: false,
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
    },
  },

  build: {
    manifest: true,
    outDir: 'resources/dist',
    emptyOutDir: false,

    rollupOptions: {
      input: path.resolve(__dirname, 'resources/js/app.js'),

      output: {
        entryFileNames: 'js/app.js',       // ✅ deterministic
        chunkFileNames: 'js/chunks/[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith('.css')) {
            return 'css/app.css'           // ✅ deterministic
          }
          return 'assets/[name][extname]'
        },
      },
    },
  },
})
