<template>
  <div class="profile-page">
    <Header />
    
    <div class="profile-container">
      <template v-if="user">
        <!-- ========== ВЕРХНЯЯ ПАНЕЛЬ ПРОФИЛЯ ========== -->
        <div class="user-info-section">
          <div class="avatar-placeholder"></div>
          <div class="user-data">
            <div class="name-edit-row">
              <h1 class="user-fullname">
                {{ user.surname }} {{ user.name }} {{ user.patronymic || '' }}
              </h1>
              <button v-if="!editing" class="edit-icon-btn" @click="startEdit" title="Редактировать профиль">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </button>
            </div>
            <p class="user-meta">
              <span>{{ user.phone }}</span>
              <span class="role-badge">{{ user.role === 'driver' ? 'Водитель' : user.role === 'loader' ? 'Грузчик' : 'Заказчик' }}</span>
            </p>
          </div>
        </div>

        <!-- ========== ФОРМА РЕДАКТИРОВАНИЯ ПРОФИЛЯ ========== -->
        <div v-if="editing" class="edit-form-container">
          <input v-model="editForm.surname" placeholder="Фамилия" />
          <input v-model="editForm.name" placeholder="Имя" />
          <input v-model="editForm.patronymic" placeholder="Отчество" />
          <input v-model="editForm.phone" placeholder="Телефон" />
          
          <div class="password-change-block">
            <button type="button" class="toggle-password-btn" @click="showPasswordFields = !showPasswordFields">
              {{ showPasswordFields ? 'Скрыть смену пароля' : 'Сменить пароль' }}
            </button>
            <div v-if="showPasswordFields" class="password-fields">
              <input type="password" v-model="editForm.current_password" placeholder="Текущий пароль" />
              <input type="password" v-model="editForm.new_password" placeholder="Новый пароль" />
              <input type="password" v-model="editForm.new_password_confirmation" placeholder="Подтверждение пароля" />
            </div>
          </div>
          
          <div class="button-group">
            <button class="save-btn" @click="saveProfile">Сохранить</button>
            <button class="cancel-btn" @click="cancelEdit">Отмена</button>
          </div>
        </div>

        <!-- ========== УВЕДОМЛЕНИЯ ========== -->
        <div v-if="notification.show" :class="['notification', notification.type]">
          {{ notification.message }}
        </div>

        <!-- ========== ТАБЫ НАВИГАЦИИ ========== -->
        <div class="profile-tabs">
          <!-- Водитель -->
          <template v-if="user.role === 'driver'">
            <button :class="{ active: activeTab === 'active_orders' }" @click="activeTab = 'active_orders'">
              В процессе
            </button>
            <button :class="{ active: activeTab === 'history' }" @click="activeTab = 'history'">
              История
            </button>
            <button :class="{ active: activeTab === 'fleet' }" @click="activeTab = 'fleet'">
              Моя техника
            </button>
            <button :class="{ active: activeTab === 'documents' }" @click="activeTab = 'documents'">
              Мои документы
            </button>
          </template>
          
          <!-- Заказчик -->
          <template v-else-if="user.role === 'customer'">
            <button :class="{ active: activeTab === 'customer_active' }" @click="activeTab = 'customer_active'">
              Активные заказы
            </button>
            <button :class="{ active: activeTab === 'customer_history' }" @click="activeTab = 'customer_history'">
              История заказов
            </button>
            <button :class="{ active: activeTab === 'favorite_drivers' }" @click="activeTab = 'favorite_drivers'">
              Избранные водители
            </button>
          </template>
          
          <!-- Грузчик -->
          <template v-else-if="user.role === 'loader'">
            <button :class="{ active: activeTab === 'loader_active' }" @click="activeTab = 'loader_active'">
              Активные заказы
            </button>
            <button :class="{ active: activeTab === 'loader_history' }" @click="activeTab = 'loader_history'">
              История
            </button>
            <button :class="{ active: activeTab === 'loader_stats' }" @click="activeTab = 'loader_stats'">
              Моя статистика
            </button>
          </template>
        </div>

        <!-- ========== ВОДИТЕЛЬ: АКТИВНЫЕ ЗАКАЗЫ ========== -->
        <div v-if="activeTab === 'active_orders' && user.role === 'driver'" class="tab-content">
          <div v-if="activeOrders.length === 0" class="no-data-message">
            Нет активных заказов
          </div>
          <div v-else v-for="order in activeOrders" :key="order.id" class="order-card">
            <div class="order-info">
              <div class="order-number">Заказ {{ order.number }}</div>
              <div class="order-route">{{ order.route }}</div>
            </div>
            <div class="order-price">{{ formatPrice(order.price) }} ₽</div>
          </div>
        </div>

        <!-- ========== ВОДИТЕЛЬ: ИСТОРИЯ ========== -->
        <div v-if="activeTab === 'history' && user.role === 'driver'" class="tab-content">
          <div class="earnings-stats">
            <div class="stat-block">
              <div class="stat-label">Выполнено рейсов</div>
              <div class="stat-number">{{ historyOrders.length }}</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-block">
              <div class="stat-label">Общий заработок</div>
              <div class="stat-number highlight">{{ formatPrice(totalEarnings) }} ₽</div>
            </div>
          </div>

          <div v-if="historyOrders.length === 0" class="no-data-message">История пуста</div>
          <div v-else v-for="order in historyOrders" :key="order.id" class="history-card">
            <div class="history-info">
              <div class="order-number">Заказ #{{ order.id }}</div>
              <div class="order-date">Завершен: {{ formatDate(order.updated_at) }}</div>
              <div class="order-route">{{ order.from_address }} <span v-if="order.to_address">→ {{ order.to_address }}</span></div>
            </div>
            <div class="order-earned">+{{ order.price }} ₽</div>
          </div>
        </div>

        <!-- ========== ВОДИТЕЛЬ: ТЕХНИКА ========== -->
        <div v-if="activeTab === 'fleet' && user.role === 'driver'" class="tab-content">
          <div class="section-header">
            <h2 class="section-title">Моя техника</h2>
            <button class="add-action-btn" @click="showVehicleModal = true">+ Добавить</button>
          </div>
          
          <div v-if="vehicles.length === 0" class="no-data-message">
            Техника не добавлена
          </div>
          
          <div v-else v-for="vehicle in vehicles" :key="vehicle.id" class="vehicle-card">
            <div class="vehicle-info">
              <div class="vehicle-name">{{ vehicle.brand }} {{ vehicle.model }}</div>
              <div class="vehicle-details">{{ vehicle.type }} • {{ vehicle.number }}</div>
            </div>
            <button class="delete-vehicle-btn" @click="openDeleteModal(vehicle.id)" title="Удалить">Удалить</button>
          </div>
        </div>

        <!-- ========== ВОДИТЕЛЬ: ДОКУМЕНТЫ ========== -->
        <div v-if="activeTab === 'documents' && user.role === 'driver'" class="tab-content">
          <div class="section-header">
            <h2 class="section-title">Водительское удостоверение</h2>
            <button class="add-action-btn" @click="openLicenseModal">Редактировать ВУ</button>
          </div>
          <div class="license-card">
            <div class="license-row">
              <span class="license-label">Номер ВУ</span>
              <span class="license-value">{{ driverLicense.licenseNumber || 'Не указан' }}</span>
            </div>
            <div class="license-row">
              <span class="license-label">Категории</span>
              <div class="categories-list">
                <span v-if="driverLicense.categories.length === 0" class="no-categories">Не указаны</span>
                <span v-else v-for="cat in driverLicense.categories" :key="cat.name" class="category-pill">
                  {{ cat.name }} <span class="date-range">{{ cat.dateFrom }} — {{ cat.dateTo }}</span>
                </span>
              </div>
            </div>
            <div class="expiration-warning" v-if="hasExpiredCategory">
              Срок действия одной или нескольких категорий истёк
            </div>
          </div>
        </div>

        <!-- ========== ЗАКАЗЧИК: АКТИВНЫЕ ЗАКАЗЫ ========== -->
        <div v-if="activeTab === 'customer_active' && user.role === 'customer'" class="tab-content">
          <div v-if="customerActiveOrders.length === 0" class="no-data-message">Нет активных заказов</div>
          <div v-else v-for="order in customerActiveOrders" :key="order.id" class="order-card">
            <div class="order-info">
              <div class="order-number">Заказ {{ order.number }}</div>
              <div class="order-route">{{ order.route }}</div>
            </div>
            <div class="order-price">{{ formatPrice(order.price) }} ₽</div>
          </div>
        </div>

        <!-- ========== ЗАКАЗЧИК: ИСТОРИЯ ЗАКАЗОВ ========== -->
        <div v-if="activeTab === 'customer_history' && user.role === 'customer'" class="tab-content">
          <div v-if="customerHistoryOrders.length === 0" class="no-data-message">История заказов пуста</div>
          <div v-else v-for="order in customerHistoryOrders" :key="order.id" class="history-card">
            <div class="history-info">
              <div class="order-number">Заказ #{{ order.id }}</div>
              <div class="order-date">Завершен: {{ formatDate(order.updated_at) }}</div>
              <div class="order-route">{{ order.from_address }} <span v-if="order.to_address">→ {{ order.to_address }}</span></div>
            </div>
            <div class="order-earned">{{ order.price }} ₽</div>
          </div>
        </div>

        <!-- ========== ЗАКАЗЧИК: ИЗБРАННЫЕ ВОДИТЕЛИ ========== -->
        <div v-if="activeTab === 'favorite_drivers' && user.role === 'customer'" class="tab-content">
          <div v-if="favoriteDrivers.length === 0" class="no-data-message">Нет избранных водителей</div>
          <div v-else v-for="driver in favoriteDrivers" :key="driver.id" class="driver-card">
            <div class="driver-info">
              <div class="driver-name">{{ driver.name }} {{ driver.surname }}</div>
              <div class="driver-rating">⭐ {{ driver.rating || 'Нет оценок' }}</div>
            </div>
          </div>
        </div>

        <!-- ========== ГРУЗЧИК: АКТИВНЫЕ ЗАКАЗЫ ========== -->
        <div v-if="activeTab === 'loader_active' && user.role === 'loader'" class="tab-content">
          <div v-if="loaderActiveOrders.length === 0" class="no-data-message">
            Нет активных заказов
          </div>
          <div v-else v-for="order in loaderActiveOrders" :key="order.id" class="order-card">
            <div class="order-info">
              <div class="order-number">Заказ {{ order.number }}</div>
              <div class="order-route">{{ order.route }}</div>
            </div>
            <div class="order-price">{{ formatPrice(order.price) }} ₽</div>
          </div>
        </div>

        <!-- ========== ГРУЗЧИК: ИСТОРИЯ ========== -->
        <div v-if="activeTab === 'loader_history' && user.role === 'loader'" class="tab-content">
          <div v-if="loaderHistoryOrders.length === 0" class="no-data-message">История пуста</div>
          <div v-else v-for="order in loaderHistoryOrders" :key="order.id" class="history-card">
            <div class="history-info">
              <div class="order-number">Заказ #{{ order.id }}</div>
              <div class="order-date">Завершен: {{ formatDate(order.updated_at) }}</div>
              <div class="order-route">{{ order.from_address }} <span v-if="order.to_address">→ {{ order.to_address }}</span></div>
            </div>
            <div class="order-earned">+{{ order.price }} ₽</div>
          </div>
        </div>

        <!-- ========== ГРУЗЧИК: СТАТИСТИКА ========== -->
        <div v-if="activeTab === 'loader_stats' && user.role === 'loader'" class="tab-content">
          <div class="earnings-stats">
            <div class="stat-block">
              <div class="stat-label">Выполнено заказов</div>
              <div class="stat-number">{{ loaderStats.totalOrders }}</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-block">
              <div class="stat-label">Общий заработок</div>
              <div class="stat-number highlight">{{ formatPrice(loaderStats.totalEarnings) }} ₽</div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- ========== МОДАЛКА: РЕДАКТИРОВАНИЕ ПРАВ ========== -->
    <div class="modal-overlay" v-if="showLicenseModal" @click.self="showLicenseModal = false">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Водительские документы</h3>
          <button class="close-modal-btn" @click="showLicenseModal = false">&times;</button>
        </div>
        <div class="modal-body">
          <input 
            v-model="licenseForm.licenseNumber" 
            placeholder="Номер водительского удостоверения"
            :class="{ 'input-error': licenseNumberError }"
          />
          <small class="hint-text">Формат: 55 55 555555 (пример: 77 55 123456)</small>
          <div v-if="licenseNumberError" class="error-text">{{ licenseNumberError }}</div>
          
          <label class="input-label">Категории и сроки действия:</label>
          <div class="categories-edit-list">
            <div v-for="cat in availableCategories" :key="cat" class="category-edit-row">
              <label class="checkbox-label">
                <input type="checkbox" :value="cat" v-model="licenseForm.selectedCategoryNames" />
                <span>Категория {{ cat }}</span>
              </label>
              <div class="date-fields" v-if="licenseForm.selectedCategoryNames.includes(cat)">
                <input type="date" v-model="licenseForm.categoryDates[cat].dateFrom" placeholder="С" />
                <input type="date" v-model="licenseForm.categoryDates[cat].dateTo" placeholder="До" />
              </div>
            </div>
          </div>
          
          <button class="modal-submit-btn" @click="submitLicenseUpdate">Сохранить</button>
        </div>
      </div>
    </div>

    <!-- ========== МОДАЛКА: ДОБАВЛЕНИЕ ТЕХНИКИ ========== -->
    <div class="modal-overlay" v-if="showVehicleModal" @click.self="showVehicleModal = false">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Добавление техники</h3>
          <button class="close-modal-btn" @click="showVehicleModal = false">&times;</button>
        </div>
        <div class="modal-body">
          <select v-model="vehicleForm.type" class="modal-select">
            <option value="" disabled>Выберите тип техники</option>
            <option value="Газель">Газель</option>
            <option value="5-тонник">5-тонник</option>
            <option value="Самосвал">Самосвал</option>
            <option value="Трал">Трал</option>
            <option value="Трактор">Трактор</option>
            <option value="Манипулятор">Манипулятор</option>
            <option value="Эвакуатор">Эвакуатор</option>
            <option value="Автовышка">Автовышка</option>
            <option value="Экскаватор">Экскаватор</option>
          </select>

          <input v-model="vehicleForm.brand" placeholder="Марка (например, КАМАЗ, Volvo, Scania)" />
          <input v-model="vehicleForm.model" placeholder="Модель (6520, FH16, R410)" />
          <input v-model="vehicleForm.number" placeholder="Гос. номер (А777АА 777)" />

          <div class="category-hint" v-if="vehicleForm.type">
            Для техники типа <strong>"{{ vehicleForm.type }}"</strong> требуется категория прав:
            <span class="category-required">{{ getRequiredCategory(vehicleForm.type) }}</span>
          </div>

          <button class="modal-submit-btn" @click="submitVehicle">Добавить технику</button>
        </div>
      </div>
    </div>

    <!-- ========== МОДАЛКА: ПОДТВЕРЖДЕНИЕ УДАЛЕНИЯ ========== -->
    <div class="modal-overlay" v-if="showDeleteModal" @click.self="showDeleteModal = false">
      <div class="modal-content delete-modal">
        <div class="modal-header">
          <h3>Удаление техники</h3>
          <button class="close-modal-btn" @click="showDeleteModal = false">&times;</button>
        </div>
        <div class="modal-body">
          <p>Вы уверены, что хотите удалить эту технику?</p>
          <div class="delete-modal-buttons">
            <button class="cancel-delete-btn" @click="showDeleteModal = false">Отмена</button>
            <button class="confirm-delete-btn" @click="confirmDelete">Удалить</button>
          </div>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
import api from '@/api/axios';

/*-------- ПЕРЕМЕННЫЕ --------*/
const router = useRouter();
const user = ref(null);
const editing = ref(false);
const activeTab = ref('active_orders');
const showPasswordFields = ref(false);
const licenseNumberError = ref('');

/*-------- ДАННЫЕ ПРОФИЛЯ --------*/
const editForm = ref({ 
  surname: '', 
  name: '', 
  patronymic: '', 
  phone: '',
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

/*-------- ДАННЫЕ ВОДИТЕЛЯ --------*/
const activeOrders = ref([]);
const historyOrders = ref([]);
const vehicles = ref([]);
const showVehicleModal = ref(false);
const showDeleteModal = ref(false);
const deleteVehicleId = ref(null);
const vehicleForm = ref({ type: '', brand: '', model: '', number: '' });

/*-------- ДАННЫЕ ПРАВ ВОДИТЕЛЯ --------*/
const availableCategories = ['B', 'C', 'D', 'BE', 'CE', 'DE'];
const showLicenseModal = ref(false);
const driverLicense = ref({
  licenseNumber: '',
  categories: []
});
const licenseForm = ref({
  licenseNumber: '',
  selectedCategoryNames: [],
  categoryDates: {}
});

availableCategories.forEach(cat => {
  licenseForm.value.categoryDates[cat] = { dateFrom: '', dateTo: '' };
});

/*-------- ДАННЫЕ ЗАКАЗЧИКА --------*/
const customerActiveOrders = ref([]);
const customerHistoryOrders = ref([]);
const favoriteDrivers = ref([]);

/*-------- ДАННЫЕ ГРУЗЧИКА --------*/
const loaderActiveOrders = ref([]);
const loaderHistoryOrders = ref([]);
const loaderStats = ref({ totalOrders: 0, totalEarnings: 0 });

/*-------- УВЕДОМЛЕНИЯ --------*/
const notification = ref({ show: false, message: '', type: 'success' });

/*-------- ВЫЧИСЛЯЕМЫЕ СВОЙСТВА --------*/
const totalEarnings = computed(() => {
  return historyOrders.value.reduce((sum, order) => sum + (order.price || 0), 0);
});

const hasExpiredCategory = computed(() => {
  return driverLicense.value.categories.some(cat => isExpired(cat.dateTo));
});

/*-------- ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ --------*/
const formatPrice = (price) => price?.toLocaleString() || '0';

const isExpired = (dateString) => {
  if (!dateString) return false;
  const [day, month, year] = dateString.split('.');
  return new Date(`${year}-${month}-${day}`) < new Date();
};

const formatDate = (dateString: string) => {
  if (!dateString) return '—';
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const showNotification = (message, type) => {
  notification.value = { show: true, message, type };
  setTimeout(() => {
    notification.value.show = false;
  }, 3000);
};

const getRequiredCategory = (type) => {
  const map = {
    'Газель': 'B, C',
    '5-тонник': 'C',
    'Самосвал': 'C, CE',
    'Трал': 'CE',
    'Трактор': 'A',
    'Манипулятор': 'C',
    'Эвакуатор': 'C',
    'Автовышка': 'C',
    'Экскаватор': 'A'
  };
  return map[type] || 'уточните';
};

/*-------- ЗАГРУЗКА ДАННЫХ --------*/
/*-------- ВОДИТЕЛЬ --------*/
const loadDriverOrders = async () => {
  try {
    const response = await api.get('/driver/orders/active');
    activeOrders.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки активных заказов водителя:', error);
  }
};

const loadDriverHistory = async () => {
  try {
    const response = await api.get('/driver/orders/history');
    historyOrders.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки истории заказов водителя:', error);
  }
};

const loadDriverData = async () => {
  try {
    const profileRes = await api.get('/driver/profile');
    const profile = profileRes.data;
    
    if (profile) {
      driverLicense.value = {
        licenseNumber: profile.license_number || '',
        categories: profile.categories?.map(cat => ({
          name: cat.name,
          dateFrom: cat.pivot?.issued_at || '',
          dateTo: cat.pivot?.expires_at || ''
        })) || []
      };
    }
  } catch (error) {
    console.error('Ошибка загрузки профиля водителя:', error);
  }
};

const loadVehicles = async () => {
  try {
    const response = await api.get('/driver/vehicles');
    vehicles.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки техники:', error);
  }
};

/*-------- ЗАКАЗЧИК --------*/
const loadCustomerActiveOrders = async () => {
  try {
    const response = await api.get('/customer/orders/active');
    customerActiveOrders.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки активных заказов заказчика:', error);
  }
};

const loadCustomerHistoryOrders = async () => {
  try {
    const response = await api.get('/customer/orders/history');
    customerHistoryOrders.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки истории заказов заказчика:', error);
  }
};

const loadFavoriteDrivers = async () => {
  try {
    const response = await api.get('/customer/favorite-drivers');
    favoriteDrivers.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки избранных водителей:', error);
  }
};

/*-------- ГРУЗЧИК --------*/
const loadLoaderActiveOrders = async () => {
  try {
    const response = await api.get('/loader/orders/active');
    loaderActiveOrders.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки активных заказов грузчика:', error);
  }
};

const loadLoaderHistoryOrders = async () => {
  try {
    const response = await api.get('/loader/orders/history');
    loaderHistoryOrders.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки истории заказов грузчика:', error);
  }
};

const loadLoaderStats = async () => {
  try {
    const response = await api.get('/loader/stats');
    loaderStats.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки статистики грузчика:', error);
  }
};

/*-------- ЗАГРУЗКА ПОЛЬЗОВАТЕЛЯ --------*/
const loadUser = async () => {
  try {
    const response = await api.get('/user');
    user.value = response.data;
    
    editForm.value = {
      surname: response.data.surname,
      name: response.data.name,
      patronymic: response.data.patronymic || '',
      phone: response.data.phone,
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    };
    
    if (user.value.role === 'driver') {
      activeTab.value = 'active_orders';
      await loadDriverOrders();
      await loadDriverHistory();
      await loadVehicles();
      await loadDriverData();
    } else if (user.value.role === 'customer') {
      activeTab.value = 'customer_active';
      await loadCustomerActiveOrders();
      await loadCustomerHistoryOrders();
      await loadFavoriteDrivers();
    } else if (user.value.role === 'loader') {
      activeTab.value = 'loader_active';
      await loadLoaderActiveOrders();
      await loadLoaderHistoryOrders();
      await loadLoaderStats();
    }
  } catch {
    router.push('/login');
  }
};

/*-------- УПРАВЛЕНИЕ ПРОФИЛЕМ --------*/
const startEdit = () => { editing.value = true; };

const cancelEdit = () => {
  editing.value = false;
  showPasswordFields.value = false;
  editForm.value = {
    surname: user.value.surname,
    name: user.value.name,
    patronymic: user.value.patronymic || '',
    phone: user.value.phone,
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  };
};

const saveProfile = async () => {
  try {
    const updateData = {
      surname: editForm.value.surname,
      name: editForm.value.name,
      patronymic: editForm.value.patronymic
    };
    
    if (editForm.value.phone !== user.value.phone) {
      updateData.phone = editForm.value.phone;
    }
    
    if (showPasswordFields.value && editForm.value.new_password) {
      updateData.current_password = editForm.value.current_password;
      updateData.password = editForm.value.new_password;
      updateData.password_confirmation = editForm.value.new_password_confirmation;
    }
    
    const response = await api.put(`/user/update/${user.value.id}`, updateData);
    user.value = response.data.user;
    editing.value = false;
    showPasswordFields.value = false;
    
    editForm.value.current_password = '';
    editForm.value.new_password = '';
    editForm.value.new_password_confirmation = '';
    
    showNotification('Профиль успешно обновлён', 'success');
  } catch (error) {
    showNotification('Ошибка обновления', 'error');
  }
};

/*-------- УПРАВЛЕНИЕ ПРАВАМИ --------*/
const openLicenseModal = () => {
  licenseForm.value.licenseNumber = driverLicense.value.licenseNumber || '';
  licenseForm.value.selectedCategoryNames = driverLicense.value.categories.map(c => c.name);
  
  availableCategories.forEach(cat => {
    licenseForm.value.categoryDates[cat] = { dateFrom: '', dateTo: '' };
  });
  
  driverLicense.value.categories.forEach(cat => {
    if (licenseForm.value.categoryDates[cat.name]) {
      licenseForm.value.categoryDates[cat.name].dateFrom = cat.dateFrom?.split('.').reverse().join('-') || '';
      licenseForm.value.categoryDates[cat.name].dateTo = cat.dateTo?.split('.').reverse().join('-') || '';
    }
  });
  
  showLicenseModal.value = true;
};

const submitLicenseUpdate = async () => {
  licenseNumberError.value = '';
  
  if (licenseForm.value.selectedCategoryNames.length === 0) {
    showNotification('Выберите хотя бы одну категорию прав', 'error');
    return;
  }
  
  const licenseNumber = licenseForm.value.licenseNumber;
  const licenseRegex = /^\d{2} \d{2} \d{6}$/;
  
  if (!licenseNumber || !licenseRegex.test(licenseNumber)) {
    licenseNumberError.value = 'Номер ВУ обязателен. Формат: 55 55 555555';
    return;
  }
  
  for (const catName of licenseForm.value.selectedCategoryNames) {
    const dates = licenseForm.value.categoryDates[catName];
    if (!dates.dateFrom || !dates.dateTo) {
      showNotification(`Для категории ${catName} заполните даты`, 'error');
      return;
    }
    if (new Date(dates.dateFrom) >= new Date(dates.dateTo)) {
      showNotification(`Для категории ${catName} дата "До" позже даты "С"`, 'error');
      return;
    }
  }
  
  try {
    const finalCategories = licenseForm.value.selectedCategoryNames.map(catName => {
      const dates = licenseForm.value.categoryDates[catName];
      return {
        name: catName,
        dateFrom: dates.dateFrom?.split('-').reverse().join('.') || '',
        dateTo: dates.dateTo?.split('-').reverse().join('.') || ''
      };
    });
    
    const payload = {
      license_number: licenseForm.value.licenseNumber,
      categories: finalCategories
    };
    
    const response = await api.put('/driver/profile', payload);
    
    if (response.data) {
      driverLicense.value = {
        licenseNumber: response.data.license_number || '',
        categories: response.data.categories?.map(cat => ({
          name: cat.name,
          dateFrom: cat.pivot?.issued_at || '',
          dateTo: cat.pivot?.expires_at || ''
        })) || []
      };
    }
    
    showLicenseModal.value = false;
    showNotification('Данные сохранены', 'success');
  } catch (error) {
    showNotification('Ошибка сохранения', 'error');
  }
};

/*-------- УПРАВЛЕНИЕ ТЕХНИКОЙ --------*/
const submitVehicle = async () => {
  if (!vehicleForm.value.type) {
    showNotification('Выберите тип техники', 'error');
    return;
  }
  if (!vehicleForm.value.brand) {
    showNotification('Введите марку', 'error');
    return;
  }
  if (!vehicleForm.value.number) {
    showNotification('Введите гос. номер', 'error');
    return;
  }

  try {
    await api.post('/driver/vehicles', vehicleForm.value);
    showVehicleModal.value = false;
    await loadVehicles();
    vehicleForm.value = { type: '', brand: '', model: '', number: '' };
    showNotification('Техника добавлена', 'success');
  } catch (error) {
    showNotification('Ошибка добавления', 'error');
  }
};

const openDeleteModal = (id) => {
  deleteVehicleId.value = id;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!deleteVehicleId.value) return;
  
  try {
    await api.delete(`/driver/vehicles/${deleteVehicleId.value}`);
    await loadVehicles();
    showDeleteModal.value = false;
    deleteVehicleId.value = null;
    showNotification('Техника удалена', 'success');
  } catch (error) {
    showNotification('Ошибка удаления', 'error');
  }
};

/*-------- ЖИЗНЕННЫЙ ЦИКЛ --------*/
onMounted(() => {
  loadUser();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ========== ОСНОВНЫЕ СТИЛИ ========== */
.profile-page {
  min-height: 100vh;
  background: #FFFFFF;
  display: flex;
  flex: 1;
  flex-direction: column;
  font-family: 'Montserrat', sans-serif;
}

.profile-container {
  max-width: 650px;
  width: 100%;
  margin: 40px auto;
  padding: 0 20px;
  flex: 1;
  box-sizing: border-box;
  min-height: 400px;
}

/* ========== ПРОФИЛЬ ПОЛЬЗОВАТЕЛЯ ========== */
.user-info-section {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 24px;
}

.avatar-placeholder {
  width: 64px;
  height: 64px;
  background-color: #E0E0E0;
  border-radius: 50%;
  flex-shrink: 0;
}

.user-data {
  flex: 1;
}

.name-edit-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.user-fullname {
  font-size: 22px;
  font-weight: 700;
  margin: 0;
  color: #000000;
}

.user-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 4px;
  font-size: 14px;
  color: #666666;
  flex-wrap: wrap;
}

.role-badge {
  background: #000000;
  color: #FFFFFF;
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 12px;
}

.edit-icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 50%;
  color: #000000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.edit-icon-btn:hover {
  background-color: #F5F5F5;
  color: #FBB03B;
}

/* ========== ФОРМА РЕДАКТИРОВАНИЯ ========== */
.edit-form-container {
  background: #FAFAFA;
  padding: 28px;
  border-radius: 24px;
  margin-bottom: 32px;
  border: 1px solid #EAEAEA;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.edit-form-container input {
  width: 100%;
  padding: 16px 20px;
  margin-bottom: 14px;
  border: 1px solid #E0E0E0;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 500;
  font-family: 'Montserrat', sans-serif;
  background: #FFFFFF;
  color: #000000;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.edit-form-container input:last-child {
  margin-bottom: 0;
}

.edit-form-container input:focus {
  outline: none;
  border-color: #000000;
  box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.04);
}

.edit-form-container input::placeholder {
  color: #999999;
}

/* ========== БЛОК СМЕНЫ ПАРОЛЯ ========== */
.password-change-block {
  margin-top: 20px;
  margin-bottom: 24px;
  border-top: 1px dashed #EAEAEA;
  padding-top: 20px;
}

.toggle-password-btn {
  background: transparent;
  border: none;
  color: #FBB03B;
  font-size: 15px;
  cursor: pointer;
  padding: 6px 0;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  transition: color 0.2s;
}

.toggle-password-btn:hover {
  color: #e09e2a;
}

.lock-icon {
  transition: transform 0.2s ease;
}

.lock-icon.is-active {
  transform: scale(1.1);
}

.password-fields {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 16px;
}

.password-fields input {
  margin-bottom: 0;
}

/* ========== КНОПКИ ФОРМЫ ========== */
.button-group {
  display: flex;
  gap: 12px;
}

.save-btn {
  background: #FBB03B;
  color: #000000;
  border: none;
  padding: 16px 24px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 16px;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  transition: background-color 0.2s, transform 0.1s;
  flex: 1;
}

.save-btn:hover {
  background-color: #e09e2a;
}

.save-btn:active {
  transform: scale(0.99);
}

.cancel-btn {
  background: transparent;
  border: 1px solid #E0E0E0;
  color: #666666;
  padding: 16px 24px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 16px;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  transition: all 0.2s;
  flex: 1;
}

.cancel-btn:hover {
  border-color: #000000;
  color: #000000;
  background: #F5F5F5;
}

/* ========== ТАБЫ ========== */
.profile-tabs {
  display: flex;
  gap: 8px;
  border-bottom: 2px solid #EAEAEA;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.profile-tabs button {
  background: none;
  border: none;
  padding: 12px 16px;
  font-size: 15px;
  font-weight: 500;
  color: #666666;
  cursor: pointer;
  white-space: nowrap;
  font-family: 'Montserrat', sans-serif;
  transition: all 0.2s;
}

.profile-tabs button.active {
  color: #000000;
  border-bottom: 2px solid #FBB03B;
  margin-bottom: -2px;
}

/* ========== СТАТИСТИКА ЗАРАБОТКА ========== */
.earnings-stats {
  background: #F5F5F5;
  border-radius: 20px;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  gap: 16px;
  flex-wrap: wrap;
}

.stat-block {
  flex: 1;
  text-align: center;
  min-width: 120px;
}

.stat-label {
  font-size: 14px;
  color: #666666;
  margin-bottom: 8px;
}

.stat-number {
  font-size: 28px;
  font-weight: 700;
  color: #000000;
  line-height: 1.2;
  word-break: break-word;
}

.stat-number.highlight {
  color: #FBB03B;
}

.stat-divider {
  width: 1px;
  height: 40px;
  background-color: #DDDDDD;
}

/* ========== КАРТОЧКИ ========== */
.no-data-message {
  text-align: center;
  color: #AAAAAA;
  padding: 40px;
  font-size: 16px;
}

.order-card {
  background: #000000;
  border-radius: 100px;
  padding: 16px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.order-info {
  flex: 1;
  min-width: 0;
}

.order-number {
  font-weight: 600;
  font-size: 16px;
  color: #FFFFFF;
}

.order-route {
  font-size: 13px;
  color: #AAAAAA;
  margin-top: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.order-price {
  font-weight: 700;
  font-size: 18px;
  color: #FBB03B;
  margin-left: 16px;
  flex-shrink: 0;
}

.history-card {
  background: #000000;
  border-radius: 100px;
  padding: 16px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.history-info {
  flex: 1;
  min-width: 0;
}

.history-card .order-number {
  font-weight: 600;
  font-size: 16px;
  color: #FFFFFF;
}

.order-date {
  font-size: 12px;
  color: #AAAAAA;
  margin-top: 4px;
}

.order-earned {
  font-weight: 700;
  font-size: 18px;
  color: #FBB03B;
  margin-left: 16px;
  flex-shrink: 0;
  white-space: nowrap;
}

.vehicle-card {
  background: #000000;
  border-radius: 100px;
  padding: 16px 24px;
  margin-bottom: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.vehicle-info {
  flex: 1;
  min-width: 0;
}

.vehicle-name {
  font-weight: 600;
  font-size: 16px;
  color: #FFFFFF;
}

.vehicle-details {
  font-size: 13px;
  color: #AAAAAA;
  margin-top: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.delete-vehicle-btn {
  background: #FF3B30;
  color: white;
  border: none;
  padding: 6px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
  margin-left: 12px;
  flex-shrink: 0;
}

.delete-vehicle-btn:hover {
  background: #cc0000;
  transform: scale(1.02);
}

.driver-card {
  background: #000000;
  border-radius: 100px;
  padding: 16px 24px;
  margin-bottom: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.driver-info {
  flex: 1;
}

.driver-name {
  font-weight: 600;
  font-size: 16px;
  color: #FFFFFF;
}

.driver-rating {
  font-size: 13px;
  color: #FBB03B;
  margin-top: 4px;
}

/* ========== ДОКУМЕНТЫ ========== */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}

.section-title {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
  color: #000000;
}

.add-action-btn {
  background: #FBB03B;
  color: #000000;
  border: none;
  padding: 8px 20px;
  border-radius: 30px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.2s;
}

.add-action-btn:hover {
  background: #e09e2a;
}

.license-card {
  background: #000000;
  border-radius: 20px;
  padding: 20px;
  color: #FFFFFF;
}

.license-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 12px 0;
  border-bottom: 1px solid #333;
  flex-wrap: wrap;
  gap: 12px;
}

.license-row:last-child {
  border-bottom: none;
}

.license-label {
  font-weight: 600;
  font-size: 14px;
  opacity: 0.8;
  min-width: 100px;
}

.license-value {
  font-weight: 600;
  text-align: right;
}

.categories-list {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  justify-content: flex-end;
}

.category-pill {
  background: #FBB03B;
  color: #000000;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
}

.category-pill .date-range {
  font-size: 11px;
  opacity: 0.8;
  margin-left: 6px;
}

.no-categories {
  color: #888888;
  font-style: italic;
}

.expiration-warning {
  margin-top: 16px;
  background: rgba(255, 59, 48, 0.15);
  color: #FF3B30;
  padding: 10px 16px;
  border-radius: 12px;
  font-size: 13px;
  text-align: center;
}

/* ========== МОДАЛЬНЫЕ ОКНА ========== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #FFFFFF;
  border-radius: 24px;
  width: 100%;
  max-width: 520px;
  padding: 32px;
  box-sizing: border-box;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.modal-header h3 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #000000;
}

.close-modal-btn {
  background: transparent;
  border: none;
  font-size: 28px;
  cursor: pointer;
  color: #999999;
  transition: color 0.2s;
  line-height: 1;
}

.close-modal-btn:hover {
  color: #000000;
}

.modal-body input,
.modal-body select {
  width: 100%;
  padding: 14px 16px;
  margin-bottom: 16px;
  border: 1px solid #E0E0E0;
  border-radius: 12px;
  font-size: 15px;
  font-family: 'Montserrat', sans-serif;
  background: #FFFFFF;
  color: #000000;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.modal-body input:focus,
.modal-body select:focus {
  outline: none;
  border-color: #000000;
  box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.04);
}

.modal-body input::placeholder {
  color: #AAAAAA;
}

.modal-select {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 16px center;
  background-size: 16px;
}

.input-label {
  font-size: 14px;
  font-weight: 600;
  color: #000000;
  margin-bottom: 8px;
  display: block;
}

.input-error {
  border-color: #FF3B30 !important;
}

.error-text {
  color: #FF3B30;
  font-size: 13px;
  margin-top: -12px;
  margin-bottom: 16px;
}

.hint-text {
  display: block;
  font-size: 12px;
  color: #888888;
  margin-top: -12px;
  margin-bottom: 16px;
}

.categories-edit-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 24px;
  max-height: 400px;
  overflow-y: auto;
  padding-right: 8px;
}

.category-edit-row {
  background: #FAFAFA;
  border: 1px solid #EAEAEA;
  border-radius: 16px;
  padding: 16px;
  transition: all 0.2s;
}

.category-edit-row:hover {
  border-color: #E0E0E0;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 16px;
  color: #000000;
  margin-bottom: 12px;
}

.checkbox-label input {
  width: 18px;
  height: 18px;
  margin: 0;
  cursor: pointer;
  accent-color: #000000;
}

.date-fields {
  display: flex;
  gap: 12px;
  margin-top: 12px;
  padding-left: 30px;
}

.date-fields input {
  flex: 1;
  margin-bottom: 0;
  padding: 12px 14px;
  font-size: 14px;
}

.category-hint {
  background: #FAFAFA;
  padding: 14px 16px;
  border-radius: 12px;
  font-size: 13px;
  margin-bottom: 20px;
  color: #666666;
  border: 1px solid #EAEAEA;
}

.category-required {
  font-weight: 700;
  color: #FBB03B;
}

.modal-submit-btn {
  background: #FBB03B;
  color: #000000;
  border: none;
  padding: 16px 24px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 16px;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  transition: background-color 0.2s;
  width: 100%;
}

.modal-submit-btn:hover {
  background-color: #e09e2a;
}

.delete-modal-buttons {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 20px;
}

.cancel-delete-btn {
  background: transparent;
  border: 2px solid #000000;
  color: #000000;
  padding: 8px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.confirm-delete-btn {
  background: #FF3B30;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.confirm-delete-btn:hover {
  background: #cc0000;
}

/* ========== УВЕДОМЛЕНИЯ ========== */
.notification {
  position: fixed;
  bottom: 80px;
  left: 50%;
  transform: translateX(-50%);
  padding: 14px 24px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  z-index: 1100;
  animation: slideUp 0.3s ease;
  white-space: nowrap;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.notification.success {
  background: #000000;
  color: #FBB03B;
}

.notification.error {
  background: #000000;
  color: #FF3B30;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

/* ========== АДАПТИВ ========== */
@media (max-width: 550px) {
  .profile-container {
    margin: 20px auto;
  }
  
  .earnings-stats {
    flex-direction: column;
  }
  
  .stat-divider {
    width: 80%;
    height: 1px;
    margin: 8px auto;
  }
  
  .order-card,
  .history-card,
  .vehicle-card,
  .driver-card {
    border-radius: 16px;
    padding: 14px 18px;
  }
  
  .license-row {
    flex-direction: column;
  }
  
  .license-label {
    min-width: auto;
  }
  
  .license-value {
    text-align: left;
  }
  
  .categories-list {
    justify-content: flex-start;
  }
  
  .notification {
    white-space: normal;
    text-align: center;
    max-width: 90%;
    width: auto;
  }
}
</style>