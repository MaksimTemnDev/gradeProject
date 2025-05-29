FROM php:8.2-fpm

# Установка системных зависимостей для Symfony
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libxslt-dev \
    libssl-dev \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_mysql \
        zip \
        intl \
        xml \
        xsl \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Настройка рабочей директории
WORKDIR /var/www
RUN chown -R www-data:www-data /var/www

# Копируем только файлы composer для кэширования слоя
COPY composer.json composer.lock symfony.lock ./

# Установка зависимостей с оптимизациями
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction --no-scripts

# Копируем остальные файлы
COPY . .

# Установка прав и оптимизация
RUN chown -R www-data:www-data /var/www \
    && composer dump-autoload --optimize \
    && php bin/console cache:clear