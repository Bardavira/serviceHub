import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'],
      refresh: true,
    }),
    vue(),
  ],
  server: {
    host: true,            // binds 0.0.0.0 inside container
    port: 5173,
    strictPort: true,
    hmr: {
      host: 'localhost',   // what the browser should use
      clientPort: 5173,
    },
  },
});