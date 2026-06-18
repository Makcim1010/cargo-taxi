<template>
  <div class="register-page">
    <div class="register-card">
      <h2>Регистрация</h2>

      <div class="role-tabs">
        <button 
          @click="form.role = 'customer'" 
          :class="{ active: form.role === 'customer' }"
        >
          Заказчик
        </button>
        <button 
          @click="form.role = 'driver'" 
          :class="{ active: form.role === 'driver' }"
        >
          Водитель
        </button>
      </div>

      <input v-model="form.surname" placeholder="Фамилия" />
      <input v-model="form.name" placeholder="Имя" />
      <input v-model="form.patronymic" placeholder="Отчество" />

      <input v-model="form.phone" placeholder="Телефон (+79991234567)" />

      <div class="password-wrapper">
        <input 
          v-model="form.password" 
          :type="showPassword ? 'text' : 'password'" 
          placeholder="Пароль"
        />
        <button 
          type="button" 
          class="password-toggle" 
          @click="showPassword = !showPassword"
        >
          <img 
            :src="showPassword ? eyeOpenIcon : eyeClosedIcon" 
            alt="toggle password"
          />
        </button>
      </div>

      <div class="password-wrapper">
        <input 
          v-model="form.password_confirmation" 
          :type="showConfirmPassword ? 'text' : 'password'" 
          placeholder="Подтверждение пароля"
        />
        <button 
          type="button" 
          class="password-toggle" 
          @click="showConfirmPassword = !showConfirmPassword"
        >
          <img 
            :src="showConfirmPassword ? eyeOpenIcon : eyeClosedIcon" 
            alt="toggle password"
          />
        </button>
      </div>

      <div v-if="form.role === 'driver'">
        <h3>Водительские права</h3>
        <input v-model="form.license_number" placeholder="Номер прав (55 55 555555)" />
        <input v-model="form.license_date" type="date" />

        <div class="categories">
          <label class="category-checkbox">
            <input type="checkbox" value="B" v-model="form.license_categories" />
            <span>Категория B</span>
          </label>
          <label class="category-checkbox">
            <input type="checkbox" value="C" v-model="form.license_categories" />
            <span>Категория C</span>
          </label>
          <label class="category-checkbox">
            <input type="checkbox" value="D" v-model="form.license_categories" />
            <span>Категория D</span>
          </label>
          <label class="category-checkbox">
            <input type="checkbox" value="BE" v-model="form.license_categories" />
            <span>Категория BE</span>
          </label>
          <label class="category-checkbox">
            <input type="checkbox" value="CE" v-model="form.license_categories" />
            <span>Категория CE</span>
          </label>
          <label class="category-checkbox">
            <input type="checkbox" value="DE" v-model="form.license_categories" />
            <span>Категория DE</span>
          </label>
        </div>

        <label class="loaders-checkbox">
          <input type="checkbox" v-model="form.has_loaders" />
          <span>Есть свои грузчики</span>
        </label>

        <input 
          v-if="form.has_loaders" 
          v-model.number="form.loaders_count" 
          type="number" 
          placeholder="Количество грузчиков"
        />
      </div>

      <button class="register-btn" @click="submitRegister">Регистрация</button>

      <div class="login-link">
        Уже есть аккаунт? 
        <router-link to="/login">Вход</router-link>
      </div>

      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { register, RegisterData } from '@/services/authService';
import { useRouter } from 'vue-router';

const router = useRouter();

import eyeOpenIcon from '@/assets/eye-open.png';
import eyeClosedIcon from '@/assets/eye-closed.png';

const form = reactive<RegisterData>({
  role: 'customer',
  surname: '',
  name: '',
  patronymic: '',
  phone: '',
  password: '',
  password_confirmation: '',
  license_number: '',
  license_date: '',
  license_categories: [],
  has_loaders: false,
  loaders_count: 0,
});

const errorMessage = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submitRegister = async () => {
  errorMessage.value = '';
  console.log('Отправляемые данные:', form);
  try {
    const response = await register(form);
    localStorage.setItem('access_token', response.access_token);
    
    // Оповещаем шапку об обновлении пользователя
    window.dispatchEvent(new Event('user-login'));
    
    // Редирект на главную
    router.push('/');
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Ошибка регистрации';
  }
};
</script>

<style scoped>
/* Стили без изменений */
.register-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #FFFFFF;
  font-family: 'Montserrat', sans-serif;
}

.register-card {
  background: #000000;
  border-radius: 16px;
  padding: 32px;
  width: 100%;
  max-width: 480px;
  margin: 20px;
}

h2 {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 24px;
  text-align: center;
  color: #FFFFFF;
}

.role-tabs {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
}

.role-tabs button {
  flex: 1;
  padding: 10px;
  border: 1px solid #FBB03B;
  background: transparent;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  color: #FFFFFF;
}

.role-tabs button.active {
  background: #FBB03B;
  border-color: #FBB03B;
  color: #000000;
}

input {
  width: 100%;
  padding: 12px;
  margin-bottom: 16px;
  border: 1px solid #333;
  border-radius: 12px;
  font-size: 14px;
  background: #FFFFFF;
  color: #000000;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #FBB03B;
}

.password-wrapper {
  position: relative;
  width: 100%;
  margin-bottom: 16px;
}

.password-wrapper input {
  margin-bottom: 0;
  padding-right: 48px;
}

.password-toggle {
  position: absolute;
  right: 12px;
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
  width: 20px;
  height: 20px;
  display: block;
}

h3 {
  font-size: 16px;
  font-weight: 500;
  margin: 16px 0 12px;
  color: #FFFFFF;
}

.categories {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}

.category-checkbox {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: #FFFFFF;
  border: 1px solid #333;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.category-checkbox:hover {
  border-color: #FBB03B;
}

.category-checkbox input {
  width: auto;
  margin: 0;
  accent-color: #FBB03B;
}

.category-checkbox span {
  font-size: 14px;
  color: #000000;
}

.loaders-checkbox {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: #FFFFFF;
  border: 1px solid #333;
  border-radius: 12px;
  cursor: pointer;
  margin-bottom: 16px;
}

.loaders-checkbox input {
  width: auto;
  margin: 0;
  accent-color: #FBB03B;
}

.loaders-checkbox span {
  font-size: 14px;
  color: #000000;
}

.register-btn {
  width: 100%;
  padding: 12px;
  background: #FBB03B;
  color: #000000;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  margin-top: 8px;
}

.register-btn:hover {
  background: #e09e2a;
}

.login-link {
  text-align: center;
  margin-top: 20px;
  font-size: 14px;
  color: #FFFFFF;
}

.login-link a {
  color: #FBB03B;
  text-decoration: none;
}

.error {
  color: #ff6b6b;
  font-size: 14px;
  margin-top: 12px;
  text-align: center;
}
</style>