<template>
  <div class="login-page">
    <div class="login-card">
      <h2>Вход</h2>

      <!-- Переключатель роли (Вкладки) -->
      <div class="role-selector">
        <button 
          type="button"
          :class="{ active: role === 'customer' }" 
          @click="role = 'customer'"
        >
          Заказчик
        </button>
        <button 
          type="button"
          :class="{ active: role === 'driver' }" 
          @click="role = 'driver'"
        >
          Водитель
        </button>
      </div>

      <!-- Поля ввода формы -->
      <input 
        v-model="phone" 
        placeholder="Телефон (+79991234567)"
        :class="{ 'input-error': errors.phone }"
      />
      <div v-if="errors.phone" class="error-text">{{ errors.phone }}</div>
      
      <div class="password-wrapper">
        <input 
          v-model="password" 
          :type="showPassword ? 'text' : 'password'" 
          placeholder="Пароль"
          :class="{ 'input-error': errors.password }"
        />
        <button type="button" class="password-toggle" @click="showPassword = !showPassword">
          <img :src="showPassword ? eyeOpenIcon : eyeClosedIcon" alt="toggle password" />
        </button>
      </div>
      <div v-if="errors.password" class="error-text">{{ errors.password }}</div>

      <!-- Главная кнопка действия -->
      <button class="login-btn" @click="submitLogin">Войти</button>

      <div class="register-link">
        Нет аккаунта? <router-link to="/register">Регистрация</router-link>
      </div>

      <!-- Общая ошибка -->
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { login } from '@/services/authService';
import { useRouter } from 'vue-router';

const router = useRouter();

import eyeOpenIcon from '@/assets/eye-open.png';
import eyeClosedIcon from '@/assets/eye-closed.png';

const phone = ref('');
const password = ref('');
const role = ref<'customer' | 'driver'>('customer');
const showPassword = ref(false);
const errorMessage = ref('');
const errors = ref<{ phone?: string; password?: string }>({});

const validateForm = (): boolean => {
  errors.value = {};
  let isValid = true;

  // Проверка телефона
  const phoneRegex = /^\+\d{11}$/;
  if (!phone.value) {
    errors.value.phone = 'Введите номер телефона';
    isValid = false;
  } else if (!phoneRegex.test(phone.value)) {
    errors.value.phone = 'Номер должен быть в формате +79991234567';
    isValid = false;
  }

  // Проверка пароля
  if (!password.value) {
    errors.value.password = 'Введите пароль';
    isValid = false;
  } else if (password.value.length < 6) {
    errors.value.password = 'Пароль должен быть не менее 6 символов';
    isValid = false;
  }

  return isValid;
};

const submitLogin = async () => {
  errorMessage.value = '';
  
  if (!validateForm()) {
    return;
  }
  
  try {
    const response = await login(phone.value, password.value);
    
    if (response.access_token) {
      localStorage.setItem('access_token', response.access_token);
      window.dispatchEvent(new Event('user-login'));
      router.push('/');
    } else {
      errorMessage.value = 'Ошибка авторизации';
    }
  } catch (error: any) {
    const status = error.response?.status;
    const data = error.response?.data;
    
    if (status === 401) {
      errorMessage.value = 'Неверный номер телефона или пароль';
    } else if (status === 422 && data?.errors) {
      // Детальные ошибки валидации
      if (data.errors.phone) {
        errors.value.phone = data.errors.phone[0];
      }
      if (data.errors.password) {
        errors.value.password = data.errors.password[0];
      }
      if (!errors.value.phone && !errors.value.password) {
        errorMessage.value = data.message || 'Ошибка валидации';
      }
    } else {
      errorMessage.value = data?.message || 'Ошибка соединения с сервером';
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #FFFFFF;
  font-family: 'Montserrat', sans-serif;
}

.login-card {
  background: #000000;
  border-radius: 16px;
  padding: 40px 32px;
  width: 100%;
  max-width: 480px;
  margin: 20px;
  box-sizing: border-box;
}

h2 {
  font-size: 26px;
  font-weight: 700;
  margin-top: 0;
  margin-bottom: 24px;
  text-align: center;
  color: #FFFFFF;
}

/* Переключатель ролей */
.role-selector {
  display: flex;
  background: #1A1A1A;
  padding: 4px;
  border-radius: 10px;
  margin-bottom: 24px;
}

.role-selector button {
  flex: 1;
  background: transparent;
  border: none;
  color: #888888;
  padding: 12px;
  border-radius: 8px;
  font-family: 'Montserrat', sans-serif;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.role-selector button.active {
  background: #FBB03B;
  color: #000000;
}

/* Поля ввода */
input {
  width: 100%;
  padding: 14px;
  margin-bottom: 8px;
  border: 2px solid #1A1A1A;
  border-radius: 12px;
  font-family: 'Montserrat', sans-serif;
  font-size: 16px;
  background: #FFFFFF;
  color: #000000;
  box-sizing: border-box;
  transition: border-color 0.2s;
}

input:focus {
  outline: none;
  border-color: #FBB03B;
}

.input-error {
  border-color: #FF3B30 !important;
}

.error-text {
  color: #FF3B30;
  font-size: 12px;
  margin-bottom: 16px;
  margin-top: -4px;
}

.password-wrapper {
  position: relative;
  width: 100%;
}

.password-wrapper input {
  margin-bottom: 8px;
  padding-right: 48px;
}

.password-toggle {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.password-toggle img {
  width: 22px;
  height: 22px;
  display: block;
}

.login-btn {
  width: 100%;
  padding: 14px;
  background: #FBB03B;
  color: #000000;
  border: none;
  border-radius: 12px;
  font-family: 'Montserrat', sans-serif;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 24px;
  transition: opacity 0.2s;
}

.login-btn:hover {
  opacity: 0.9;
}

.register-link {
  text-align: center;
  margin-top: 24px;
  font-size: 15px;
  color: #FFFFFF;
}

.register-link a {
  color: #FBB03B;
  text-decoration: none;
  font-weight: 600;
}

.register-link a:hover {
  text-decoration: underline;
}

.error {
  color: #FF3B30;
  font-size: 14px;
  font-weight: 500;
  margin-top: 16px;
  margin-bottom: 0;
  text-align: center;
  background: rgba(255, 59, 48, 0.1);
  padding: 10px;
  border-radius: 8px;
}
</style>