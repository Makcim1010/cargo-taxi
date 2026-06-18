<template>
  <div class="news-page">
    <Header />
    
    <div class="news-container">
      <!-- Заголовок страницы -->
      <h1 class="page-title">Новости компании</h1>
      
      <!-- Состояние загрузки или отсутствия данных в БД -->
      <div v-if="newsList.length === 0" class="no-news">
        На данный момент новостей нет
      </div>
      
      <!-- Список новостей из БД -->
      <div v-else class="news-grid">
        <div 
          v-for="news in newsList" 
          :key="news.id" 
          class="news-card"
        >
          <div class="news-card-header">
            <!-- Категория новости (Бейдж) -->
            <span class="news-category">{{ news.category }}</span>
            <!-- Дата публикации -->
            <span class="news-date">{{ news.date }}</span>
          </div>
          
          <div class="news-card-body">
            <h2 class="news-title">{{ news.title }}</h2>
            <p class="news-excerpt">{{ news.excerpt }}</p>
          </div>
          
          <div class="news-card-footer">
            <!-- Кнопка перехода к полной статье (можно настроить router-link) -->
            <button class="read-more-btn" @click="goToNewsDetail(news.id)">
              <span>Читать полностью</span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
import api from '@/api/axios';

const router = useRouter();

// Массив для хранения новостей из базы данных
const newsList = ref([]);

// Метод для загрузки новостей из БД
const loadNews = async () => {
  try {
    // Раскомментируйте при интеграции с вашим бэкендом:
    // const response = await api.get('/news');
    // newsList.value = response.data;

    // ВРЕМЕННЫЕ ДЕМО-ДАННЫЕ ДЛЯ РАЗРАБОТКИ:
    newsList.value = [
      {
        id: 1,
        category: 'Обновление',
        date: '11.06.2026',
        title: 'Запуск новой программы лояльности для водителей и грузчиков',
        excerpt: 'Мы снизили базовую комиссию системы при достижении определенных ежедневных планов по объемам и количеству выполненных заказов. Подробности уже в личном кабинете.'
      },
      {
        id: 2,
        category: 'Важное',
        date: '08.06.2026',
        title: 'Изменение правил вывода денежных средств через СБП',
        excerpt: 'Теперь средства поступают на баланс вашей карты мгновенно. Мы убрали задержки транзакций со стороны платежного шлюза для вашего удобства.'
      },
      {
        id: 3,
        category: 'Полезное',
        date: '01.06.2026',
        title: 'Как подготовить спецтехнику к летнему рабочему сезону',
        excerpt: 'Собрали основные рекомендации по техническому обслуживанию гидравлических систем экскаваторов и самосвалов в условиях повышенных температур.'
      }
    ];
  } catch (error) {
    console.error('Ошибка при загрузке новостей из БД:', error);
  }
};

const goToNewsDetail = (id: number) => {
  router.push(`/news/${id}`); // Переход на страницу конкретной новости
};

onMounted(() => {
  loadNews();
});
</script>

<style scoped>
@import url('https://googleapis.com');

.news-page {
  min-height: 100vh;
  background: #FFFFFF; /* Белый фон */
  display: flex;
  flex-direction: column;
  font-family: 'Montserrat', sans-serif;
  font-size: 18px; /* Базовый размер */
  color: #000000;
}

.news-container {
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
  margin-bottom: 36px;
  color: #000000;
  letter-spacing: -0.5px;
}

/* Сетка карточек новостей */
.news-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr); /* Две колонки на десктопе */
  gap: 28px;
  margin-bottom: 40px;
}

/* Чёрные скругленные карточки */
.news-card {
  background: #000000; /* Чёрный фон карточек */
  color: #FFFFFF;     /* Текст на тёмном фоне */
  border-radius: 24px; /* Овальные края */
  padding: 32px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  transition: transform 0.2s ease;
}

.news-card:hover {
  transform: translateY(-2px); /* Легкий интерактивный подъем при наведении */
}

/* Верхняя строка карточки (Категория и дата) */
.news-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.news-category {
  background: #1A1A1A;
  color: #FBB03B; /* Категория подсвечена акцентом */
  font-size: 13px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.news-date {
  font-size: 14px;
  color: #888888;
  font-weight: 500;
}

/* Тело карточки */
.news-card-body {
  flex: 1;
  margin-bottom: 28px;
}

.news-title {
  margin: 0 0 12px 0;
  font-size: 22px;
  font-weight: 700;
  line-height: 1.4;
  color: #FFFFFF;
}

.news-excerpt {
  margin: 0;
  font-size: 15px;
  color: #CCCCCC;
  line-height: 1.6;
}

/* Ссылка-кнопка перехода к новости */
.read-more-btn {
  background: transparent;
  border: none;
  color: #FBB03B; /* Фирменный желтый акцент */
  font-family: 'Montserrat', sans-serif;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 0;
  transition: color 0.2s ease;
}

.read-more-btn:hover {
  color: #e09e2a;
}

.read-more-btn svg {
  transition: transform 0.2s ease;
}

.read-more-btn:hover svg {
  transform: translateX(4px); /* Стрелочка сдвигается вправо при ховере */
}

.no-news {
  color: #666666;
  text-align: center;
  padding: 48px;
}

/* Адаптивность под планшеты и мобильные устройства */
@media (max-width: 992px) {
  .news-grid {
    grid-template-columns: 1fr; /* Перестраивается в одну колонку */
    gap: 20px;
  }
}

@media (max-width: 768px) {
  .page-title {
    font-size: 26px;
    margin-bottom: 24px;
  }
  .news-card {
    padding: 24px;
  }
  .news-title {
    font-size: 19px;
  }
}
</style>
