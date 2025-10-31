import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    react(),
    tailwindcss(),
  ],
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        main: 'resources/js/spectra/main.tsx',
        app: 'resources/css/app.css',
      },
    },
  },
  resolve: {
    alias: {
      '@': '/resources/js/spectra',
    },
  },
});
