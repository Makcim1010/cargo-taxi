FROM php:8.2-apache

# Устанавливаем зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Устанавливаем расширения PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создаём файл базы данных SQLite
RUN touch /var/www/html/database/database.sqlite
RUN chmod 666 /var/www/html/database/database.sqlite

# Копируем проект
COPY . /var/www/html/

# Устанавливаем зависимости
RUN composer install --no-dev --ignore-platform-req=php

# Генерируем ключ (если нет в .env)
RUN php artisan key:generate --force

# Кешируем маршруты и конфиги
RUN php artisan route:cache
RUN php artisan config:cache
RUN php artisan view:cache

# Выполняем миграции
RUN php artisan migrate --force

# Права на папки
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Включаем mod_rewrite
RUN a2enmod rewrite

# Копируем .htaccess если есть
COPY .htaccess /var/www/html/public/.htaccess 2>/dev/null || true

EXPOSE 80