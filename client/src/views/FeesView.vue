<template>
  <div class="fees-page">
    <Header />
    
    <div class="fees-container">
      <!-- Заголовок страницы -->
      <h1 class="page-title">Расчет оплаты и комиссии</h1>
      
      <!-- Главное правило системы -->
      <div class="base-rule-card">
        <div class="rule-icon">
          <i class="fas fa-info-circle"></i>
        </div>
        <p class="rule-text">
          За каждый выполненный заказ, вне зависимости от формы оплаты, с кошелька исполнителя списывается базовая комиссия в размере <span class="highlight">15%</span> от стоимости заказа.
        </p>
      </div>

      <!-- Раздел программ лояльности -->
      <h2 class="section-title">Программы лояльности и бонусы</h2>
      
      <div class="cards-grid">
        
        <!-- КАРТОЧКА 1: ВОДИТЕЛЬ -->
        <div class="loyalty-card">
          <div class="card-header">
            <div class="icon-wrapper">
              <i class="fas fa-truck-moving"></i>
            </div>
            <h3>Режим: Водитель</h3>
          </div>
          <p class="card-subtitle">Ежедневные бонусы снижения комиссии в зависимости от выработки за сутки:</p>
          
          <ul class="bonus-list">
            <li>
              <span class="condition">10 заказов <span class="or">или</span> от 15 000 ₽</span>
              <span class="result-badge">Комиссия 13%</span>
            </li>
            <li>
              <span class="condition">15 заказов <span class="or">или</span> от 20 000 ₽</span>
              <span class="result-badge">Комиссия 11%</span>
            </li>
            <li>
              <span class="condition">20 заказов <span class="or">или</span> от 25 000 ₽</span>
              <span class="result-badge">Комиссия 9%</span>
            </li>
          </ul>
        </div>

        <!-- КАРТОЧКА 2: ГРУЗЧИК -->
        <div class="loyalty-card">
          <div class="card-header">
            <div class="icon-wrapper">
              <i class="fas fa-box-open"></i>
            </div>
            <h3>Режим: Грузчик</h3>
          </div>
          <p class="card-subtitle">Ежедневные бонусы снижения комиссии в зависимости от выработки за сутки:</p>
          
          <ul class="bonus-list">
            <li>
              <span class="condition">10 заказов <span class="or">или</span> от 7 000 ₽</span>
              <span class="result-badge">Комиссия 13%</span>
            </li>
            <li>
              <span class="condition">15 заказов <span class="or">или</span> от 10 000 ₽</span>
              <span class="result-badge">Комиссия 11%</span>
            </li>
            <li>
              <span class="condition">20 заказов <span class="or">или</span> от 13 000 ₽</span>
              <span class="result-badge">Комиссия 9%</span>
            </li>
          </ul>
        </div>

        <!-- КАРТОЧКА 3: ЗАКАЗЧИК -->
        <div class="loyalty-card customer-card">
          <div class="card-header">
            <div class="icon-wrapper customer-icon">
              <i class="fas fa-user-tie"></i>
            </div>
            <h3>Режим: Заказчик</h3>
          </div>
          <p class="card-subtitle">Бонусы и скидки на создание заявок в течение текущего календарного месяца:</p>
          
          <div class="customer-bonus-box">
            <div class="bonus-main">
              <span class="bonus-discount">-10%</span>
              <span class="bonus-target">На каждый 5-й заказ</span>
            </div>
            <p class="bonus-condition-text">* Скидка применяется автоматически к каждому пятому заказу стоимостью от <br>1 000 ₽ в текущем месяце.</p>
          </div>
        </div>

      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
</script>

<style scoped>
@import url('https://googleapis.com');

/* Базовая верстка страницы */
.fees-page {
  min-height: 100vh;
  background: #FFFFFF; /* Белый фон */
  display: flex;
  flex-direction: column;
  font-family: 'Montserrat', sans-serif;
  font-size: 18px;
  color: #000000;
}

.fees-container {
  max-width: 1200px;
  width: 100%;
  margin: 40px auto;
  padding: 0 20px;
  flex: 1;
  box-sizing: border-box;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  margin-top: 0;
  margin-bottom: 32px;
  color: #000000;
  letter-spacing: -0.5px;
}

/* Карточка базового правила */
.base-rule-card {
  display: flex;
  align-items: center;
  gap: 20px;
  background: #F9F9F9;
  border: 1px solid #EAEAEA;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 48px;
}

.rule-icon {
  font-size: 28px;
  color: #FBB03B; /* Ваш акцентный цвет */
  display: flex;
  align-items: center;
}

.rule-text {
  margin: 0;
  font-size: 17px;
  font-weight: 500;
  line-height: 1.6;
  color: #333333;
}

.rule-text .highlight {
  font-weight: 700;
  color: #000000;
  background: #FBB03B;
  padding: 2px 6px;
  border-radius: 4px;
}

.section-title {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 28px;
  color: #000000;
}

/* Сетка карточек */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-bottom: 40px;
}

/* Общий стиль черных скругленных карточек */
.loyalty-card {
  background: #000000; /* Чёрный фон по дизайн-системе */
  color: #FFFFFF;     /* Текст на тёмном фоне */
  border-radius: 24px; /* Овальные современные скругления */
  padding: 32px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}

.icon-wrapper {
  width: 44px;
  height: 44px;
  background: #1A1A1A;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #FBB03B; /* Иконки под цвет акцента */
}

.card-header h3 {
  margin: 0;
  font-size: 19px;
  font-weight: 700;
  letter-spacing: -0.3px;
}

.card-subtitle {
  margin: 0 0 24px 0;
  font-size: 14px;
  color: #888888;
  line-height: 1.5;
}

/* Списки бонусов */
.bonus-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
  flex: 1;
}

.bonus-list li {
  display: flex;
  flex-direction: column;
  gap: 6px;
  border-bottom: 1px solid #1A1A1A;
  padding-bottom: 12px;
}

.bonus-list li:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.condition {
  font-size: 15px;
  font-weight: 600;
  color: #FFFFFF;
}

.condition .or {
  color: #555555;
  font-weight: 500;
  font-size: 13px;
}

.result-badge {
  align-self: flex-start;
  background: transparent;
  color: #FBB03B; /* Текст бонуса яркий */
  font-size: 15px;
  font-weight: 700;
}

/* Специфика карточки Заказчика */
.customer-bonus-box {
  background: #1A1A1A;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  flex: 1;
  justify-content: center;
}

.bonus-main {
  display: flex;
  align-items: center;
  gap: 16px;
}

.bonus-discount {
  font-size: 36px;
  font-weight: 800;
  color: #FBB03B;
  line-height: 1;
}

.bonus-target {
  font-size: 16px;
  font-weight: 700;
  color: #FFFFFF;
  line-height: 1.3;
}

.bonus-condition-text {
  margin: 0;
  font-size: 12px;
  color: #666666;
  line-height: 1.5;
}

/* Адаптивность под планшеты и мобильные */
@media (max-width: 1024px) {
  .cards-grid {
    grid-template-columns: 1fr; /* Карточки выстраиваются в один ряд вертикально */
    gap: 20px;
  }
}

@media (max-width: 768px) {
  .page-title {
    font-size: 26px;
    margin-bottom: 24px;
  }
  .base-rule-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
}
</style>
