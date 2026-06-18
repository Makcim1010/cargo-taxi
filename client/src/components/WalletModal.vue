<template>
  <div class="modal-overlay" @click.self="closeModal">
    <div class="modal-content">
      <!-- Кнопка закрытия крестиком -->
      <button class="close-modal-btn" @click="closeModal">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>

      <!-- Шапка -->
      <div class="modal-header">
        <h3>Управление балансом</h3>
      </div>

      <div class="modal-body">
        <!-- Аккуратный скругленный переключатель режимов -->
        <div class="action-tabs">
          <button 
            type="button"
            :class="{ active: operationType === 'deposit' }" 
            @click="operationType = 'deposit'"
          >
            Пополнить
          </button>
          <button 
            type="button"
            :class="{ active: operationType === 'withdraw' }" 
            @click="operationType = 'withdraw'"
          >
            Вывести
          </button>
        </div>

        <!-- Поле ввода суммы -->
        <div class="input-group">
          <label class="input-label">Укажите сумму (₽)</label>
          <div class="input-field-wrapper">
            <input 
              type="number" 
              v-model.number="amount" 
              placeholder="0" 
              min="1"
            />
          </div>
        </div>

        <!-- Кнопки быстрого выбора суммы -->
        <div v-if="operationType === 'deposit'" class="presets-grid">
          <button 
            v-for="preset in depositPresets" 
            :key="preset" 
            type="button" 
            class="preset-card"
            :class="{ 'is-selected': amount === preset }"
            @click="amount = preset"
          >
            +{{ preset.toLocaleString() }} ₽
          </button>
        </div>

        <!-- Поля для вывода средств -->
        <div v-if="operationType === 'withdraw'" class="input-group withdrawal-section">
          <label class="input-label">Номер банковской карты или СБП</label>
          <div class="input-field-wrapper">
            <input 
              type="text" 
              v-model="paymentDetails" 
              placeholder="2200 0000 0000 0000"
            />
          </div>
        </div>

        <!-- Статусные сообщения -->
        <p v-if="errorMessage" class="message error-msg">{{ errorMessage }}</p>
        <p v-if="successMessage" class="message success-msg">{{ successMessage }}</p>

        <!-- Главная кнопка действия -->
        <button class="submit-action-btn" @click="handleTransaction">
          {{ operationType === 'deposit' ? 'Перейти к оплате' : 'Запросить вывод средств' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import api from '@/api/axios';

const emit = defineEmits(['close', 'transaction-success']);

const operationType = ref<'deposit' | 'withdraw'>('deposit');
const amount = ref<number | ''>('');
const paymentDetails = ref('');
const errorMessage = ref('');
const successMessage = ref('');
const isLoading = ref(false);
const balance = ref(0);

const depositPresets = [500, 1000, 2000, 5000];

const loadBalance = async () => {
  try {
    const response = await api.get('/user');
    balance.value = response.data.balance || 0;
  } catch (error) {
    console.error('Ошибка загрузки баланса:', error);
  }
};

watch(operationType, () => {
  amount.value = '';
  paymentDetails.value = '';
  errorMessage.value = '';
  successMessage.value = '';
});

const closeModal = () => {
  emit('close');
};

const handleTransaction = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  if (!amount.value || amount.value <= 0) {
    errorMessage.value = 'Пожалуйста, укажите корректную сумму';
    return;
  }

  if (operationType.value === 'withdraw' && !paymentDetails.value.trim()) {
    errorMessage.value = 'Укажите реквизиты для вывода средств';
    return;
  }

  if (operationType.value === 'withdraw' && amount.value > balance.value) {
    errorMessage.value = 'Недостаточно средств на балансе';
    return;
  }

  isLoading.value = true;

  try {
    if (operationType.value === 'deposit') {
      const response = await api.post('/user/test-deposit', { amount: amount.value });
      balance.value = response.data.balance;
      successMessage.value = `Счёт пополнен на ${amount.value} ₽`;
    } else {
      const response = await api.post('/user/test-withdraw', { amount: amount.value });
      balance.value = response.data.balance;
      successMessage.value = `Выведено ${amount.value} ₽ на карту ${paymentDetails.value}`;
    }

    setTimeout(() => {
      emit('transaction-success');
      closeModal();
    }, 1500);
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Ошибка операции';
  } finally {
    isLoading.value = false;
  }
};

loadBalance();
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* Размытие и центрирование подложки */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
  font-family: 'Montserrat', sans-serif;
}

/* Новая чистая карточка модального окна */
.modal-content {
  background: #FFFFFF;
  border-radius: 24px;
  width: 100%;
  max-width: 460px;
  padding: 36px;
  box-sizing: border-box;
  position: relative;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
}

/* Кнопка закрытия */
.close-modal-btn {
  position: absolute;
  top: 24px;
  right: 24px;
  background: transparent;
  border: none;
  color: #999999;
  cursor: pointer;
  padding: 6px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s, color 0.2s;
}

.close-modal-btn:hover {
  background-color: #F5F5F5;
  color: #000000;
}

.modal-header {
  margin-bottom: 28px;
}

.modal-header h3 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #000000;
  letter-spacing: -0.5px;
}

.modal-body {
  display: flex;
  flex-direction: column;
}

/* Современные плоские вкладки переключения */
.action-tabs {
  display: flex;
  background: #F5F5F5;
  padding: 4px;
  border-radius: 12px;
  margin-bottom: 28px;
}

.action-tabs button {
  flex: 1;
  background: transparent;
  border: none;
  color: #777777;
  padding: 12px;
  border-radius: 10px;
  font-family: 'Montserrat', sans-serif;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-tabs button.active {
  background: #000000;
  color: #FFFFFF;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Текстовые подписи полей */
.input-group {
  margin-bottom: 20px;
}

.input-label {
  font-size: 14px;
  font-weight: 600;
  color: #000000;
  margin-bottom: 8px;
  display: block;
}

/* Обертка полей для правильных отступов */
.input-field-wrapper input {
  width: 100%;
  padding: 16px 20px;
  border: 1px solid #E0E0E0;
  border-radius: 12px;
  background: #FFFFFF;
  color: #000000;
  font-family: 'Montserrat', sans-serif;
  font-size: 16px;
  font-weight: 500;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.input-field-wrapper input:focus {
  outline: none;
  border-color: #000000;
  box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.04);
}

/* Сетка карточек быстрой суммы */
.presets-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-bottom: 28px;
}

.preset-card {
  background: #FFFFFF;
  border: 1px solid #E0E0E0;
  color: #000000;
  padding: 14px;
  border-radius: 12px;
  font-family: 'Montserrat', sans-serif;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.preset-card:hover {
  border-color: #000000;
  background: #F9F9F9;
}

/* Красивое состояние выбранной суммы */
.preset-card.is-selected {
  background: #000000;
  color: #FFFFFF;
  border-color: #000000;
}

.withdrawal-section {
  margin-top: 4px;
}

/* Главная кнопка */
.submit-action-btn {
  background: #FBB03B;
  color: #000000;
  border: none;
  padding: 16px;
  border-radius: 12px;
  font-family: 'Montserrat', sans-serif;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.1s ease, background-color 0.2s;
  margin-top: 8px;
}

.submit-action-btn:hover {
  background-color: #e09e2a;
}

/* Тексты ошибок и статусов */
.message {
  font-size: 14px;
  font-weight: 500;
  text-align: center;
  margin: 0 0 16px 0;
}

.error-msg { color: #FF3B30; }
.success-msg { color: #FBB03B; font-weight: 600; }

/* Сброс стандартных стрелочек у инпутов типа number */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>