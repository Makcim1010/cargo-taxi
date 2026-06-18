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

# Копируем проект
COPY . /var/www/html/

# Создаём папку и файл базы данных SQLite
RUN mkdir -p /var/www/html/database && \
    touch /var/www/html/database/database.sqlite && \
    chmod 666 /var/www/html/database/database.sqlite

# Создаём .env из .env.example (если есть)
RUN if [ -f /var/www/html/.env.example ]; then cp /var/www/html/.env.example /var/www/html/.env; else echo "APP_KEY=" > /var/www/html/.env; fi

# Устанавливаем зависимости
RUN composer install --no-dev --ignore-platform-req=php

# Генерируем ключ
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

EXPOSE 80