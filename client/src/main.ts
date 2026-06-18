import { createApp } from 'vue'
import App from './App.vue'
import router from './router'  // ← импорт роутера

import '@fortawesome/fontawesome-free/css/all.min.css'

createApp(App)
  .use(router)  // ← подключение роутера
  .mount('#app')