<template>
  <div class="customer-home">
    <div class="container-inner">
      <h1 class="page-title">Создание заказа</h1>

      <div class="order-form-card">
        <!-- Тип перевозки -->
        <div class="form-group full-width">
          <label class="group-label">Тип перевозки</label>
          <div class="radio-group">
            <label class="radio-option" :class="{ active: orderType === 'point_to_point' }">
              <input type="radio" v-model="orderType" value="point_to_point" />
              <span class="custom-radio"></span>
              <span class="radio-text">От точки А до точки Б</span>
            </label>
            <label class="radio-option" :class="{ active: orderType === 'one_point' }">
              <input type="radio" v-model="orderType" value="one_point" />
              <span class="custom-radio"></span>
              <span class="radio-text">На одну точку (спецтехника)</span>
            </label>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Адрес отправления</label>
            <div class="input-wrapper">
              <input v-model="fromAddress" placeholder="Укажите город, улицу, дом" />
            </div>
          </div>

          <div class="form-group" v-if="orderType === 'point_to_point'">
            <label>Адрес назначения</label>
            <div class="input-wrapper">
              <input v-model="toAddress" placeholder="Укажите пункт прибытия" />
            </div>
          </div>

          <div class="form-group">
            <label>Тип техники</label>
            <div class="input-wrapper select-wrapper">
              <select v-model="selectedVehicleType">
                <option value="" disabled>Выберите тип транспорта</option>
                <option v-for="type in availableVehicleTypes" :key="type" :value="type">{{ type }}</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Желаемое время подачи</label>
            <div class="input-wrapper">
              <input type="datetime-local" v-model="desiredTime" />
            </div>
          </div>
        </div>

        <!-- Нужны ли грузчики -->
        <div class="form-group full-width" v-if="orderType === 'point_to_point'">
          <label class="checkbox-label" :class="{ checked: needLoaders }">
            <input type="checkbox" v-model="needLoaders" />
            <span class="custom-checkbox"></span>
            <span class="checkbox-text">Нужны грузчики для погрузки (+100 ₽ к заказу)</span>
          </label>
        </div>

        <div class="form-group full-width" v-if="needLoaders">
          <label>Объём погрузочных работ</label>
          <div class="input-wrapper">
            <input v-model="workVolume" placeholder="Например: 2 тонны, 10 паллет, подъем на 4 этаж" />
          </div>
        </div>

        <div class="form-group full-width">
          <label>Комментарий к заказу</label>
          <div class="input-wrapper">
            <textarea v-model="comment" placeholder="Опишите характер груза или особенности..." rows="3"></textarea>
          </div>
        </div>

        <div class="price-summary">
          <span class="price-label">Итого к оплате:</span>
          <span class="price-amount">{{ totalPrice }} ₽</span>
        </div>

        <div class="form-actions">
          <button class="submit-btn" @click="createOrder">
            <span>Создать заказ</span>
          </button>
        </div>
      </div>

      <!-- Активные заказы -->
      <div class="orders-section" v-if="activeOrders.length > 0">
        <h2 class="section-title">Мои активные заказы</h2>
        <div class="orders-list">
          <div v-for="order in activeOrders" :key="order.id" class="order-card">
            <div class="order-header">
              <span class="order-id">Заказ #{{ order.id }}</span>
              <span class="order-status-badge" :class="order.status">{{ getStatusText(order.status) }}</span>
            </div>
            <div class="order-info-body">
              <p><strong>Откуда:</strong> {{ order.from_address }}</p>
              <p v-if="order.to_address"><strong>Куда:</strong> {{ order.to_address }}</p>
              <p><strong>Тип:</strong> {{ order.vehicle_type }}</p>
              <p><strong>Грузчики:</strong> {{ order.need_loaders ? 'Требуются' : 'Не требуются' }}</p>
              <p class="order-price"><strong>Цена:</strong> <span>{{ order.price }} ₽</span></p>
            </div>
            <div class="order-footer-actions" 
                 v-if="order.status === 'completed_by_driver' || order.status === 'completed_by_loader'">
              <button class="confirm-btn" @click="confirmOrder(order.id)">
                Подтвердить выполнение
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, inject, computed } from 'vue';
import api from '@/api/axios';

const showNotification = inject('showNotification');

const orderType = ref('point_to_point');
const fromAddress = ref('');
const toAddress = ref('');
const selectedVehicleType = ref('');
const desiredTime = ref('');
const comment = ref('');
const needLoaders = ref(false);
const workVolume = ref('');
const activeOrders = ref([]);
let intervalId: number | null = null;

const transportTypes = {
  point_to_point: ['Газель', '5-тонник', 'Самосвал', 'Трал'],
  one_point: ['Трактор', 'Манипулятор', 'Эвакуатор', 'Автовышка', 'Экскаватор']
};

const availableVehicleTypes = computed(() => {
  return transportTypes[orderType.value as keyof typeof transportTypes] || [];
});

const totalPrice = computed(() => {
  return needLoaders.value ? 600 : 500;
});

const loadActiveOrders = async () => {
  try {
    const response = await api.get('/customer/orders/active');
    activeOrders.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки заказов:', error);
  }
};

const createOrder = async () => {
  if (!fromAddress.value) {
    showNotification('Введите адрес отправления', 'error');
    return;
  }

  if (orderType.value === 'point_to_point' && !toAddress.value) {
    showNotification('Введите адрес назначения', 'error');
    return;
  }

  if (!selectedVehicleType.value) {
    showNotification('Выберите тип техники', 'error');
    return;
  }

  try {
    await api.post('/orders', {
      from_address: fromAddress.value,
      to_address: toAddress.value,
      vehicle_type: selectedVehicleType.value,
      order_type: orderType.value,
      desired_time: desiredTime.value,
      comment: comment.value,
      need_loaders: needLoaders.value,
      work_volume: workVolume.value,
      price: totalPrice.value
    });

    showNotification('Заказ создан!', 'success');
    await loadActiveOrders();

    fromAddress.value = '';
    toAddress.value = '';
    selectedVehicleType.value = '';
    desiredTime.value = '';
    comment.value = '';
    needLoaders.value = false;
    workVolume.value = '';
  } catch (error: any) {
    showNotification(error.response?.data?.message || 'Ошибка создания заказа', 'error');
  }
};

const confirmOrder = async (id: number) => {
  try {
    await api.post(`/customer/orders/${id}/confirm`);
    showNotification('Заказ подтверждён! Деньги переведены исполнителям.', 'success');
    await loadActiveOrders();
  } catch (error: any) {
    showNotification(error.response?.data?.message || 'Ошибка подтверждения заказа', 'error');
  }
};

const getStatusText = (status: string) => {
  const map: Record<string, string> = {
    'searching': 'Поиск водителя',
    'waiting_for_loader': 'Поиск грузчика',
    'active': 'В работе',
    'completed_by_driver': 'Выполнен водителем',
    'completed_by_loader': 'Выполнен грузчиком',
    'confirmed_by_customer': '✅ Завершён',
    'cancelled': 'Отменён'
  };
  return map[status] || status;
};

onMounted(() => {
  loadActiveOrders();
  intervalId = setInterval(loadActiveOrders, 10000);
});

onBeforeUnmount(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

.customer-home {
  background: #FFFFFF;
  font-family: 'Montserrat', sans-serif;
  color: #000000;
  min-height: 100vh;
  padding: 40px 0;
}

.container-inner {
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 20px;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 32px;
}

.order-form-card {
  background: #000000;
  color: #FFFFFF;
  border-radius: 24px;
  padding: 40px;
  display: flex;
  flex-direction: column;
  gap: 28px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.form-group.full-width {
  grid-column: span 2;
}

.form-group label:not(.radio-option):not(.checkbox-label) {
  font-size: 14px;
  font-weight: 600;
  color: #888888;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.input-wrapper input,
.input-wrapper select,
.input-wrapper textarea {
  width: 100%;
  background: #1A1A1A;
  border: 1px solid #2A2A2A;
  border-radius: 12px;
  padding: 16px 20px;
  color: #FFFFFF;
  font-family: 'Montserrat', sans-serif;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.2s;
}

.input-wrapper input:focus,
.input-wrapper select:focus,
.input-wrapper textarea:focus {
  outline: none;
  border-color: #FBB03B;
  background: #222222;
}

.radio-group {
  display: flex;
  gap: 20px;
  margin-top: 4px;
}

.radio-option {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 14px;
  background: #1A1A1A;
  border: 1px solid #2A2A2A;
  border-radius: 14px;
  padding: 18px 24px;
  cursor: pointer;
  transition: all 0.2s;
}

.radio-option input {
  display: none;
}

.custom-radio {
  width: 20px;
  height: 20px;
  border: 2px solid #444444;
  border-radius: 50%;
  display: inline-block;
  position: relative;
  transition: all 0.2s;
}

.radio-option.active {
  border-color: #FBB03B;
  background: #222222;
}
.radio-option.active .custom-radio {
  border-color: #FBB03B;
}
.radio-option.active .custom-radio::after {
  content: '';
  width: 10px;
  height: 10px;
  background: #FBB03B;
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.radio-text {
  font-size: 16px;
  font-weight: 600;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 14px;
  cursor: pointer;
  padding: 8px 0;
}
.checkbox-label input {
  display: none;
}
.custom-checkbox {
  width: 22px;
  height: 22px;
  border: 2px solid #444444;
  border-radius: 6px;
  background: #1A1A1A;
  display: inline-block;
  position: relative;
  transition: all 0.2s;
}
.checkbox-label.checked .custom-checkbox {
  border-color: #FBB03B;
  background: #FBB03B;
}
.checkbox-label.checked .custom-checkbox::after {
  content: '✓';
  color: #000000;
  font-weight: 800;
  font-size: 14px;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.checkbox-text {
  font-size: 16px;
  font-weight: 600;
}

.form-actions {
  margin-top: 12px;
}
.submit-btn {
  width: 100%;
  background: #FBB03B;
  color: #000000;
  border: none;
  border-radius: 14px;
  padding: 20px;
  font-family: 'Montserrat', sans-serif;
  font-size: 18px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}
.submit-btn:hover {
  background: #e09e2a;
}
.submit-btn:active {
  transform: scale(0.99);
}

.orders-section {
  margin-top: 56px;
}
.section-title {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 24px;
}
.orders-list {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.order-card {
  border: 1px solid #EAEAEA;
  background: #F9F9F9;
  border-radius: 20px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #EAEAEA;
  padding-bottom: 14px;
  margin-bottom: 16px;
}
.order-id {
  font-weight: 700;
  font-size: 17px;
}
.order-status-badge {
  font-size: 13px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 6px;
  background: #EEEEEE;
  color: #666666;
}
.order-status-badge.completed_by_driver,
.order-status-badge.completed_by_loader {
  background: #FFF3CD;
  color: #856404;
}
.order-status-badge.active {
  background: #D1ECF1;
  color: #0C5460;
}
.order-info-body p {
  margin: 0 0 10px 0;
  font-size: 15px;
  line-height: 1.5;
}
.order-price span {
  color: #FBB03B;
  font-weight: 700;
  font-size: 18px;
}
.order-footer-actions {
  margin-top: 16px;
}
.confirm-btn {
  width: 100%;
  background: #000000;
  color: #FFFFFF;
  border: none;
  border-radius: 10px;
  padding: 12px;
  font-family: 'Montserrat', sans-serif;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.confirm-btn:hover {
  background: #222222;
}

@media (max-width: 768px) {
  .form-grid,
  .orders-list {
    grid-template-columns: 1fr;
  }
  .form-group.full-width {
    grid-column: span 1;
  }
  .radio-group {
    flex-direction: column;
    gap: 12px;
  }
  .order-form-card {
    padding: 24px;
  }
}

.price-summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: #1A1A1A;
  border-radius: 12px;
  margin-top: 8px;
}

.price-label {
  font-size: 16px;
  font-weight: 600;
  color: #888888;
}

.price-amount {
  font-size: 24px;
  font-weight: 700;
  color: #FBB03B;
}

.animate-fade {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.order-status-badge.waiting_for_loader {
  background: #FFF3CD;
  color: #856404;
}
</style>