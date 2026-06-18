<template>
  <div class="home-page">
    <Header />
    
    <div class="home-container">
      <!-- Водитель -->
      <DriverHome v-if="user?.role === 'driver'" />
      
      <!-- Грузчик -->
      <LoaderHome v-else-if="user?.role === 'loader'" />
      
      <!-- Заказчик -->
      <CustomerHome v-else-if="user?.role === 'customer'" />
      
      <!-- Не авторизован -->
      <div v-else class="guest-view">
        <h1>Добро пожаловать в АстреБ</h1>
        <p>Войдите или зарегистрируйтесь, чтобы продолжить</p>
        <div class="guest-actions">
          <router-link to="/login" class="btn primary">Войти</router-link>
          <router-link to="/register" class="btn secondary">Регистрация</router-link>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
import CustomerHome from '@/components/CustomerHome.vue';
import DriverHome from '@/components/DriverHome.vue';
import LoaderHome from '@/components/LoaderHome.vue';
import api from '@/api/axios';

const user = ref(null);

const loadUser = async () => {
  try {
    const response = await api.get('/user');
    user.value = response.data;
  } catch {
    user.value = null;
  }
};

onMounted(loadUser);
</script>

<style scoped>
.home-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #FFFFFF;
}

.home-container {
  flex: 1;
  padding: 20px;
}

.guest-view {
  text-align: center;
  padding: 60px 20px;
}

.guest-view h1 {
  font-size: 36px;
  margin-bottom: 16px;
}

.guest-view p {
  font-size: 18px;
  color: #666;
  margin-bottom: 24px;
}

.guest-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
}

.btn {
  padding: 12px 32px;
  border-radius: 12px;
  font-weight: 600;
  text-decoration: none;
}

.btn.primary {
  background: #FBB03B;
  color: #000000;
}

.btn.secondary {
  background: transparent;
  color: #000000;
  border: 2px solid #000000;
}
</style>