<template>
  <div id="app">
    <router-view />
    
    <!-- Глобальные уведомления -->
    <div v-if="notification.show" class="global-notification" :class="notification.type">
      {{ notification.message }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, provide, onBeforeUnmount } from 'vue';

const notification = ref({ show: false, message: '', type: 'success' });

let timeoutId: number | null = null;

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
  notification.value = { show: true, message, type };
  
  if (timeoutId) clearTimeout(timeoutId);
  timeoutId = setTimeout(() => {
    notification.value.show = false;
  }, 3000);
};

provide('showNotification', showNotification);

onBeforeUnmount(() => {
  if (timeoutId) clearTimeout(timeoutId);
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Montserrat', sans-serif;
  background: #FFFFFF;
}

.global-notification {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  padding: 16px 32px;
  border-radius: 12px;
  font-family: 'Montserrat', sans-serif;
  font-weight: 600;
  font-size: 15px;
  z-index: 9999;
  animation: slideUp 0.3s ease;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  max-width: 90%;
  text-align: center;
}

.global-notification.success {
  background: #000000;
  color: #FBB03B;
}

.global-notification.error {
  background: #000000;
  color: #FF3B30;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}
</style>