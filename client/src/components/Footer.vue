<template>
  <footer class="footer">
    <div class="footer-container">
      
      <!-- Верхний ряд с контентом -->
      <div class="footer-main-row">
        
        <!-- Блок 1: Логотип -->
        <div class="footer-col logo-col">
          <!-- Замените @/assets/logo.png на ваш путь к логотипу -->
          <img src="@/assets/logo.png" alt="ЯСТРЕБ" class="footer-logo-img" />
        </div>

        <!-- Блок 2: Телефон -->
        <div class="footer-col contact-col">
          <span class="contact-label">Телефон</span>
          <a href="tel:+776453" class="contact-value">+77 64 53</a>
        </div>

        <!-- Блок 3: Почта -->
        <div class="footer-col contact-col">
          <span class="contact-label">Почта</span>
          <a href="mailto:admin@hh-soft.ru" class="contact-value">admin@hh-soft.ru</a>
        </div>



        <!-- Блок 5: Меню (Колонка 1) -->
        <div class="footer-col links-col">
          <a href="#">Найти спецтехнику</a>
          <a href="#">Новости</a>
          <a href="#">Отзывы</a>
        </div>

        <!-- Блок 6: Меню (Колонка 2) -->
        <div class="footer-col links-col">
          <a href="#">О компании</a>
          <a href="#">Стоимость</a>
        </div>

      </div>

      <!-- Нижний ряд: Копирайт -->
      <div class="footer-bottom-row">
        <p class="copyright">©Грузо перевозки 2000-2023. Все права защищены</p>
      </div>

    </div>
  </footer>
</template>

<script setup lang="ts">
// Логика для подвала не требуется
</script>

<style scoped>
@import url('https://googleapis.com');

.footer {
  background: #000000; /* Чистый черный цвет */
  color: #FFFFFF;
  padding: 60px 0 30px 0; /* Обнулили боковые отступы у самого подвала */
  font-family: 'Montserrat', sans-serif;
  width: 100%;
  margin: 0;
  box-sizing: border-box;
}

.footer-container {
  max-width: 1200px; /* Ограничиваем ширину только контента, чтобы он не разъезжался */
  width: 100%;
  margin: 0 auto; /* Центрируем контент */
  padding: 0 40px; /* Добавляем боковые отступы безопасности для контента */
  display: flex;
  flex-direction: column;
  gap: 40px;
  box-sizing: border-box;
}

/* Главная строка флексов */
.footer-main-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 30px;
}

.footer-col {
  display: flex;
  flex-direction: column;
}

/* Стили логотипа */
.footer-logo-img {
  height: 42px;
  width: auto;
  display: block;
}

/* Стили контактов */
.contact-col {
  gap: 8px;
}

.contact-label {
  font-size: 13px;
  color: #888888;
  font-weight: 500;
}

.contact-value {
  font-size: 18px;
  font-weight: 700;
  color: #FFFFFF;
  text-decoration: none;
  transition: color 0.2s;
}

.contact-value:hover {
  color: #FBB03B;
}

/* Соцсети */
.socials-col {
  flex-direction: row;
  gap: 12px;
  align-items: center;
}

.social-link {
  display: block;
}

.social-icon {
  width: 40px;
  height: 40px;
  object-fit: contain;
  transition: transform 0.2s;
}

.social-icon:hover {
  transform: translateY(-2px);
}

/* Навигационные ссылки справа */
.links-col {
  gap: 12px;
}

.links-col a {
  color: #FFFFFF;
  text-decoration: none;
  font-size: 15px;
  font-weight: 500;
  white-space: nowrap;
  transition: color 0.2s;
}

.links-col a:hover {
  color: #FBB03B;
}

/* Нижний блок: Копирайт */
.footer-bottom-row {
  border-top: 1px solid #1A1A1A;
  padding-top: 20px;
}

.copyright {
  font-size: 13px;
  color: #555555;
  margin: 0;
}

/* Адаптив под мобилки */
@media (max-width: 992px) {
  .footer-main-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 40px 20px;
  }
}

@media (max-width: 576px) {
  .footer-main-row {
    grid-template-columns: 1fr;
  }
  .footer-container {
    padding: 0 20px; /* Меньше отступы на смартфонах */
  }
}
</style>
