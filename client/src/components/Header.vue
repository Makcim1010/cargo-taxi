<template>
  <header class="header">
    <div class="header-container">
      <!-- Логотип -->
      <router-link to="/" class="logo-link">
        <div class="logo">
          <img src="@/assets/logo.png" alt="АСТРЕБ" class="logo-img" />
        </div>
      </router-link>
      
      <!-- Навигация -->
      <nav class="nav-menu">
        <a href="#">Спец техника</a>
        <a href="#">Отзывы</a>
        <a href="#">Контакты</a>
        <router-link to="/news" active-class="active-link">Новости</router-link>
        <router-link to="/fees" active-class="active-link">Расчет оплаты и комиссии</router-link>
      </nav>
      
      <div class="header-right">
        <!-- Телефон -->
        <button class="phone-btn">
          <img src="@/assets/phone-icon.png" alt="phone" class="phone-icon" />
          <span>77-64-53</span>
        </button>
        
        <!-- Авторизован -->
        <template v-if="user">
          <div class="user-menu-container" ref="menuContainer">
            <button class="user-dropdown-toggle" @click="toggleMenu">
              <span class="user-name-text">{{ user.name }}</span>
              <svg 
                class="dropdown-arrow" 
                :class="{ 'is-active': isMenuOpen }"
                width="14" 
                height="14" 
                viewBox="0 0 24 24" 
                fill="none" 
                stroke="currentColor" 
                stroke-width="2.5"
              >
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
            </button>

            <!-- Выпадающее меню -->
            <div class="user-dropdown-menu" v-if="isMenuOpen">
              <router-link to="/profile" class="dropdown-item" @click="closeMenu">
                <i class="fas fa-user-circle menu-icon"></i>
                <span>Мой профиль</span>
              </router-link>

              <!-- Кошелёк -->
              <div class="dropdown-item wallet-item" @click="openWalletModal">
                <i class="fas fa-wallet menu-icon"></i>
                <div class="wallet-info">
                  <span class="wallet-title">Кошелёк</span>
                  <span class="wallet-balance">{{ user.balance?.toLocaleString() || 0 }} ₽</span>
                </div>
              </div>

              <!-- Смена роли (открывает модалку) -->
              <button class="dropdown-item role-toggle-btn" @click="openRoleModal">
                <i class="fas fa-exchange-alt menu-icon"></i>
                <div class="role-info">
                  <span class="role-title">Роль:</span>
                  <span class="role-current">
                    {{ user.role === 'driver' ? 'Водитель' : user.role === 'loader' ? 'Грузчик' : 'Заказчик' }}
                  </span>
                </div>
              </button>

              <hr class="dropdown-divider" />

              <button class="dropdown-item logout-item-btn" @click="logout">
                <i class="fas fa-sign-out-alt menu-icon"></i>
                <span>Выйти</span>
              </button>
            </div>
          </div>
        </template>
        
        <!-- Не авторизован -->
        <template v-else>
          <router-link to="/login" class="login-btn">
            <img src="@/assets/login-icon.png" alt="login" class="login-icon" />
            Вход
          </router-link>
        </template>
      </div>
    </div>

    <!-- Модалка кошелька -->
    <WalletModal 
      v-if="showWalletModal" 
      @close="showWalletModal = false"
      @transaction-success="loadUser"
    />

    <!-- Модалка выбора роли -->
    <div class="modal-overlay" v-if="showRoleModal" @click.self="showRoleModal = false">
      <div class="modal-content role-modal">
        <div class="modal-header">
          <h3>Выберите роль</h3>
          <button class="close-modal-btn" @click="showRoleModal = false">&times;</button>
        </div>
        <div class="modal-body">
          <button 
            class="role-option" 
            :class="{ active: user.role === 'customer' }"
            @click="switchRole('customer')"
          >
            <span class="role-icon"></span>
            <div class="role-info">
              <span class="role-name">Заказчик</span>
              <span class="role-desc">Заказывать технику и услуги</span>
            </div>
          </button>
          
          <button 
            class="role-option" 
            :class="{ active: user.role === 'driver' }"
            @click="switchRole('driver')"
          >
            <span class="role-icon"></span>
            <div class="role-info">
              <span class="role-name">Водитель</span>
              <span class="role-desc">Управлять техникой и заказами</span>
            </div>
          </button>
          
          <button 
            class="role-option" 
            :class="{ active: user.role === 'loader' }"
            @click="switchRole('loader')"
          >
            <span class="role-icon"></span>
            <div class="role-info">
              <span class="role-name">Грузчик</span>
              <span class="role-desc">Выполнять погрузочные работы</span>
            </div>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '@/api/axios';
import WalletModal from '@/components/WalletModal.vue';

const router = useRouter();
const route = useRoute();
const user = ref<any>(null);
const isMenuOpen = ref(false);
const menuContainer = ref<HTMLElement | null>(null);
const showWalletModal = ref(false);
const showRoleModal = ref(false);

/*-------- Загрузка пользователя --------*/
const loadUser = async () => {
  const token = localStorage.getItem('access_token');
  if (token) {
    try {
      const response = await api.get('/user');
      user.value = response.data;
    } catch (error) {
      localStorage.removeItem('access_token');
      user.value = null;
    }
  } else {
    user.value = null;
  }
};

/*-------- Выход --------*/
const logout = async () => {
  try {
    await api.post('/logout');
  } catch (error) {
    console.error('Logout error:', error);
  }
  localStorage.removeItem('access_token');
  user.value = null;
  isMenuOpen.value = false;
  router.push('/');
};

/*-------- Модалка кошелька --------*/
const openWalletModal = () => {
  showWalletModal.value = true;
  isMenuOpen.value = false;
};

/*-------- Модалка выбора роли --------*/
const openRoleModal = () => {
  showRoleModal.value = true;
  isMenuOpen.value = false;
};

const switchRole = async (role: string) => {
  try {
    const response = await api.put('/user/change-role', { role });
    user.value.role = response.data.role;
    showRoleModal.value = false;
    window.location.reload();
  } catch (error) {
    console.error('Ошибка изменения роли:', error);
    alert('Ошибка изменения роли');
  }
};

/*-------- Управление меню --------*/
const toggleMenu = (event: Event) => {
  event.stopPropagation();
  isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
  isMenuOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
  if (menuContainer.value && !menuContainer.value.contains(event.target as Node)) {
    isMenuOpen.value = false;
  }
};

/*-------- Жизненный цикл --------*/
onMounted(() => {
  loadUser();
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

watch(() => route.path, () => {
  loadUser();
  isMenuOpen.value = false;
});
</script>

<style scoped>
/*========== ОСНОВНЫЕ СТИЛИ ==========*/
.header {
  background: #FFFFFF;
  padding: 16px 32px;
  position: sticky;
  top: 0;
  z-index: 100;
  font-family: 'Montserrat', sans-serif;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto;
}

.logo-link {
  text-decoration: none;
  cursor: pointer;
}

.logo-img {
  height: 45px;
  width: auto;
  display: block;
}

/*========== НАВИГАЦИЯ ==========*/
.nav-menu {
  display: flex;
  gap: 32px;
  align-items: center;
}

.nav-menu a {
  color: #000000;
  text-decoration: none;
  font-size: 18px;
  transition: color 0.2s;
}

.nav-menu a:hover {
  color: #FBB03B;
}

/*========== ПРАВАЯ ЧАСТЬ ==========*/
.header-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

.phone-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #FBB03B;
  border: none;
  color: #000000;
  padding: 10px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 18px;
  font-weight: 500;
  transition: background 0.2s;
}

.phone-btn:hover {
  background: #e09e2a;
}

.phone-icon {
  width: 20px;
  height: 20px;
  filter: brightness(0);
}

/*========== МЕНЮ ПОЛЬЗОВАТЕЛЯ ==========*/
.user-menu-container {
  position: relative;
}

.user-dropdown-toggle {
  background: transparent;
  border: none;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 8px;
  transition: background 0.2s;
}

.user-dropdown-toggle:hover {
  background-color: #F5F5F5;
}

.user-name-text {
  color: #000000;
  font-size: 18px;
  font-weight: 600;
}

.dropdown-arrow {
  color: #000000;
  transition: transform 0.2s;
}

.dropdown-arrow.is-active {
  transform: rotate(180deg);
  color: #FBB03B;
}

.user-dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: #000000;
  border-radius: 12px;
  width: 260px;
  padding: 8px 0;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  z-index: 200;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: #FFFFFF;
  text-decoration: none;
  background: transparent;
  border: none;
  width: 100%;
  text-align: left;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
  box-sizing: border-box;
}

.dropdown-item:hover {
  background-color: #1A1A1A;
  color: #FBB03B;
}

.menu-icon {
  width: 18px;
  font-size: 14px;
  text-align: center;
}

.wallet-item {
  cursor: pointer;
}

.wallet-item:hover {
  background-color: #1A1A1A;
}

.wallet-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.wallet-title {
  font-size: 12px;
  color: #888888;
}

.wallet-balance {
  font-size: 15px;
  font-weight: 700;
  color: #FBB03B;
}

.role-info {
  display: flex;
  gap: 8px;
  align-items: center;
  flex: 1;
}

.role-title {
  color: #888888;
}

.role-current {
  font-weight: 700;
}

.dropdown-divider {
  border: none;
  border-top: 1px solid #222222;
  margin: 8px 0;
}

.logout-item-btn {
  color: #FF3B30;
}

.logout-item-btn:hover {
  background-color: #2a1a1a;
  color: #FF6B6B;
}

/*========== КНОПКА ВХОДА ==========*/
.login-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  border: 1px solid #000000;
  color: #000000;
  padding: 10px 24px;
  border-radius: 8px;
  font-size: 18px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s;
}

.login-btn:hover {
  border-color: #FBB03B;
  color: #FBB03B;
}

.login-icon {
  width: 16px;
  height: 16px;
  filter: brightness(0);
}

.login-btn:hover .login-icon {
  filter: brightness(0) invert(1) sepia(1) saturate(5) hue-rotate(360deg);
}

/* ========== МОДАЛЬНЫЕ ОКНА (ДОБАВИТЬ В КОНЕЦ) ========== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-content {
  background: #FFFFFF;
  border-radius: 24px;
  width: 90%;
  max-width: 400px;
  padding: 28px;
  box-sizing: border-box;
  position: relative;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.modal-header h3 {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  color: #000000;
}

.close-modal-btn {
  background: transparent;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #999;
  transition: color 0.2s;
}

.close-modal-btn:hover {
  color: #000;
}

/* ========== ВЫБОР РОЛИ ========== */
.role-modal {
  max-width: 360px;
}

.role-option {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #F8F8F8;
  border: 2px solid transparent;
  border-radius: 16px;
  cursor: pointer;
  text-align: left;
  margin-bottom: 12px;
  transition: all 0.2s;
}

.role-option:hover {
  background: #F0F0F0;
  border-color: #FBB03B;
}

.role-option.active {
  background: #FBB03B;
  border-color: #FBB03B;
}

.role-icon {
  font-size: 32px;
}

.role-info {
  flex: 1;
}

.role-name {
  display: block;
  font-weight: 700;
  font-size: 16px;
  color: #000000;
}

.role-desc {
  display: block;
  font-size: 12px;
  color: #666666;
  margin-top: 2px;
}

.role-option.active .role-name,
.role-option.active .role-desc {
  color: #000000;
}

.active-link {
  color: #FBB03B !important;
}

/*========== АДАПТИВ ==========*/
@media (max-width: 768px) {
  .nav-menu {
    display: none;
  }
  
  .user-name-text {
    font-size: 16px;
  }
  
  .phone-btn span {
    display: none;
  }
  
  .phone-btn {
    padding: 10px 12px;
  }
}
</style>