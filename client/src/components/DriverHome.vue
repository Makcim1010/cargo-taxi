<template>
  <div class="driver-home">
    <div class="container-inner">
      <h1 class="page-title">Заказы на линии</h1>

      <!-- Доступные заказы -->
      <div v-if="loading" class="loading-text">Загрузка...</div>

      <div v-else-if="availableOrders.length === 0" class="no-orders">
        Нет заказов, подходящих под вашу категорию
      </div>

      <div v-else class="orders-list">
        <div v-for="order in availableOrders" :key="order.id" class="order-card">
          <div class="order-header">
            <span class="order-number">Заказ #{{ order.id }}</span>
            <span class="order-status searching">Поиск</span>
          </div>
          <div class="order-info">
            <p><strong>Откуда:</strong> {{ order.from_address }}</p>
            <p v-if="order.to_address"><strong>Куда:</strong> {{ order.to_address }}</p>
            <p><strong>Тип:</strong> {{ order.vehicle_type }}</p>
            <p v-if="order.need_loaders"><strong>Грузчики:</strong> Требуются (+100 ₽)</p>
            <p><strong>Цена:</strong> {{ order.price }} ₽</p>
          </div>
          <div class="order-actions">
            <button class="accept-btn" @click="acceptOrder(order.id, false)">
              Принять (без грузчиков)
            </button>
            <button 
              v-if="order.need_loaders" 
              class="accept-with-loader-btn" 
              @click="acceptOrder(order.id, true)"
            >
              Принять (с грузчиками)
            </button>
          </div>
        </div>
      </div>

      <!-- Активные заказы водителя -->
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
              <p><strong>Откуда:</strong> {{ order.from_address }}</p>
              <p v-if="order.to_address"><strong>Куда:</strong> {{ order.to_address }}</p>
              <p><strong>Тип:</strong> {{ order.vehicle_type }}</p>
              <p v-if="order.need_loaders">
                <strong>Грузчик:</strong> 
                {{ order.loader_id ? 'Назначен ✅' : '⏳ Ожидание...' }}
              </p>
              <p><strong>Цена:</strong> {{ order.price }} ₽</p>
            </div>
            <div class="order-actions">
              <!-- Если грузчик не нужен или уже назначен -->
              <button 
                v-if="order.status === 'active' && !order.need_loaders"
                class="complete-btn"
                @click="completeOrder(order.id)"
              >
                Завершить работу
              </button>
              
              <!-- Если нужен грузчик и он уже назначен -->
              <button 
                v-if="order.status === 'active' && order.need_loaders && order.loader_id"
                class="complete-btn"
                @click="completeOrder(order.id)"
              >
                Завершить свою часть
              </button>
              
              <!-- Ожидание грузчика -->
              <span v-else-if="order.status === 'waiting_for_loader'" class="waiting-text">
                ⏳ Ожидание грузчика...
              </span>
              
              <!-- Водитель завершил, ждёт грузчика -->
              <span v-else-if="order.status === 'completed_by_driver'" class="completed-text">
                ✅ Вы завершили, ждём грузчика
              </span>
              
              <!-- Грузчик завершил, ждёт заказчика -->
              <span v-else-if="order.status === 'completed_by_loader'" class="completed-text">
                ✅ Грузчик завершил, ждём подтверждения
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
    const available = await api.get('/driver/orders/available');
    availableOrders.value = available.data;

    const active = await api.get('/driver/orders/active');
    activeOrders.value = active.data;
  } catch (error) {
    console.error('Ошибка загрузки:', error);
  } finally {
    loading.value = false;
  }
};

const acceptOrder = async (id: number, withLoader: boolean) => {
  try {
    await api.post(`/driver/orders/${id}/accept`, { with_loader: withLoader });
    showNotification('Заказ принят!', 'success');
    await loadData();
  } catch (error: any) {
    showNotification(error.response?.data?.message || 'Ошибка при принятии заказа', 'error');
  }
};

const completeOrder = async (id: number) => {
  try {
    await api.post(`/driver/orders/${id}/complete`);
    showNotification('Работа завершена!', 'success');
    await loadData();
  } catch (error: any) {
    showNotification(error.response?.data?.message || 'Ошибка при завершении заказа', 'error');
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

.driver-home {
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
}

.order-status.searching {
  background: #FBB03B;
  color: #000000;
}

.order-status.waiting_for_loader {
  background: #FFF3CD;
  color: #856404;
}

.order-status.active {
  background: #D1ECF1;
  color: #0C5460;
}

.order-status.completed_by_driver,
.order-status.completed_by_loader {
  background: #D4EDDA;
  color: #155724;
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
  display: flex;
  gap: 8px;
}

.accept-btn {
  flex: 1;
  background: #FBB03B;
  color: #000000;
  border: none;
  padding: 12px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s;
}

.accept-btn:hover {
  background: #e09e2a;
}

.accept-with-loader-btn {
  flex: 1;
  background: #4CAF50;
  color: #FFFFFF;
  border: none;
  padding: 12px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s;
}

.accept-with-loader-btn:hover {
  background: #388E3C;
}

.complete-btn {
  width: 100%;
  background: #4CAF50;
  color: #FFFFFF;
  border: none;
  padding: 12px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s;
}

.complete-btn:hover {
  background: #388E3C;
}

.waiting-text {
  color: #856404;
  font-weight: 600;
}

.completed-text {
  color: #0C5460;
  font-weight: 600;
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