import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
    server: {
    // Разрешаем Vite принимать запросы со ссылки devtunnels
    allowedHosts: [
      'nftnp94s-5173.euw.devtunnels.ms'
    ],
    // Настраиваем автоматическое перенаправление API запросов
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000', // локальный бэкенд Laravel
        changeOrigin: true,
        secure: false,
      }
    }
  },

  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
})

