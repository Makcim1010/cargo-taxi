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
    libzip-dev \
    libpq-dev

# Устанавливаем расширения PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo_pgsql

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем проект
COPY . /var/www/html/

# Копируем .env.example в .env
RUN cp /var/www/html/.env.example /var/www/html/.env

# Устанавливаем корень сайта на public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf

# Устанавливаем зависимости
RUN composer install --no-dev --ignore-platform-req=php

# Генерируем ключ
RUN php artisan key:generate --force

# Кешируем
RUN php artisan route:cache
RUN php artisan config:cache
RUN php artisan view:cache

# Проверяем соединение с БД и выполняем миграции
RUN php artisan migrate --force -v

# Права на папки
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Включаем mod_rewrite
RUN a2enmod rewrite

EXPOSE 80