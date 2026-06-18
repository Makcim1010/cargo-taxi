<template>
  <div class="loader-home">
    <div class="container-inner">
      <h1 class="page-title">Доступные заказы (погрузка)</h1>

      <div v-if="loading" class="loading-text">Загрузка...</div>

      <div v-else-if="availableOrders.length === 0" class="no-orders">
        Нет заказов с погрузочными работами
      </div>

      <div v-else class="orders-list">
        <div v-for="order in availableOrders" :key="order.id" class="order-card">
          <div class="order-header">
            <span class="order-number">Заказ #{{ order.id }}</span>
            <span class="order-status searching">Поиск грузчика</span>
          </div>
          <div class="order-info">
            <p><strong>Адрес:</strong> {{ order.from_address }}</p>
            <p><strong>Техника:</strong> {{ order.vehicle_type }}</p>
            <p><strong>Объём работ:</strong> {{ order.work_volume || 'Не указан' }}</p>
            <p><strong>Цена загрузчика:</strong> {{ order.loader_price }} ₽</p>
          </div>
          <div class="order-actions">
            <button class="accept-btn" @click="acceptOrder(order.id)">
              Взяться за работу
            </button>
          </div>
        </div>
      </div>

      <!-- Активные заказы грузчика -->
      <div class="orders-section" v-if="activeOrders.length > 0">
        <h2 class="section-title">Мои активные заказы</h2>
        <div class="orders-list">
          <div v-for="order in activeOrders" :key="order.id" class="order-card active-card">
            <div class="order-header">
              <span class="order-number">Заказ #{{ order.id }}</span>
              <span class="order-status" :class="order.status">
                {{ getStatusText(order.status) }}
              </span>
            </div>
            <div class="order-info">
              <p><strong>Адрес:</strong> {{ order.from_address }}</p>
              <p><strong>Техника:</strong> {{ order.vehicle_type }}</p>
              <p><strong>Объём работ:</strong> {{ order.work_volume || 'Не указан' }}</p>
              <p><strong>Цена загрузчика:</strong> {{ order.loader_price }} ₽</p>
            </div>
            <div class="order-actions">
              <button 
                v-if="order.status === 'active'"
                class="complete-btn"
                @click="completeOrder(order.id)"
              >
                Завершить работу
              </button>
              <span v-else-if="order.status === 'completed_by_loader'" class="completed-text">
                ✅ Вы завершили, ждём водителя
              </span>
              <span v-else-if="order.status === 'completed_by_driver'" class="completed-text">
                ✅ Водитель завершил, ждём вас
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, inject } from 'vue';
import api from '@/api/axios';

const showNotification = inject('showNotification');

const availableOrders = ref([]);
const activeOrders = ref([]);
const loading = ref(true);
let intervalId: number | null = null;

const loadData = async () => {
  try {
    const available = await api.get('/loader/orders/available');
    availableOrders.value = available.data;

    const active = await api.get('/loader/orders/active');
    activeOrders.value = active.data;
  } catch (error) {
    console.error('Ошибка загрузки:', error);
  } finally {
    loading.value = false;
  }
};

const acceptOrder = async (id: number) => {
  try {
    await api.post(`/loader/orders/${id}/accept`);
    showNotification('Работа взята!', 'success');
    await loadData();
  } catch (error: any) {
    showNotification(error.response?.data?.message || 'Ошибка при принятии работы', 'error');
  }
};

const completeOrder = async (id: number) => {
  try {
    await api.post(`/loader/orders/${id}/complete`);
    showNotification('Работа завершена!', 'success');
    await loadData();
  } catch (error: any) {
    showNotification(error.response?.data?.message || 'Ошибка при завершении работы', 'error');
  }
};

const getStatusText = (status: string) => {
  const map: Record<string, string> = {
    'searching': 'Поиск',
    'waiting_for_loader': '⏳ Ожидание грузчика',
    'active': 'В работе',
    'completed_by_driver': '✅ Водитель завершил',
    'completed_by_loader': '✅ Грузчик завершил',
    'confirmed_by_customer': '✅ Завершён'
  };
  return map[status] || status;
};

onMounted(() => {
  loadData();
  intervalId = setInterval(loadData, 10000);
});

onBeforeUnmount(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>
<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

.loader-home {
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

.loading-text,
.no-orders {
  text-align: center;
  padding: 60px;
  color: #888;
  font-size: 18px;
  background: #F9F9F9;
  border-radius: 24px;
}

.orders-list {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.order-card {
  background: #000000;
  color: #FFFFFF;
  border-radius: 24px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #2A2A2A;
  padding-bottom: 14px;
  margin-bottom: 16px;
}

.order-number {
  font-weight: 700;
  font-size: 17px;
}

.order-status {
  font-size: 13px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 6px;
  background: #FBB03B;
  color: #000000;
}

.order-status.active {
  background: #D1ECF1;
  color: #0C5460;
}
.order-status.completed_by_loader {
  background: #FFF3CD;
  color: #856404;
}
.order-status.confirmed_by_customer {
  background: #D4EDDA;
  color: #155724;
}

.order-info p {
  margin: 0 0 10px 0;
  font-size: 15px;
  line-height: 1.5;
}

.order-actions {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #2A2A2A;
}

.accept-btn {
  width: 100%;
  background: #FBB03B;
  color: #000000;
  border: none;
  border-radius: 10px;
  padding: 12px;
  font-family: 'Montserrat', sans-serif;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.accept-btn:hover {
  background: #e09e2a;
}

.complete-btn {
  width: 100%;
  background: #4CAF50;
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
.complete-btn:hover {
  background: #388E3C;
}

.orders-section {
  margin-top: 56px;
}
.section-title {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 24px;
  color: #000000;
}
.active-card {
  background: #1A1A1A;
}

@media (max-width: 768px) {
  .orders-list {
    grid-template-columns: 1fr;
  }
}
</style>