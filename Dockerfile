# Multi-stage Dockerfile for Laravel app (PHP 8.1)
FROM php:8.2-fpm-bullseye AS base

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    libxml2-dev \
    libssl-dev \
    libmagickwand-dev --no-install-recommends && rm -rf /var/lib/apt/lists/*

RUN pecl install imagick && docker-php-ext-enable imagick

RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype && \
    docker-php-ext-install -j$(nproc) gd mbstring pdo pdo_mysql zip xml opcache

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

## Node builder stage
FROM node:18-bullseye AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --silent
COPY resources resources
COPY vite.config.js .
COPY public public
RUN npm run build

## Final stage
FROM base AS final
WORKDIR /var/www/html

# Copy composer files first to leverage layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Copy application code
COPY . .

# Copy built assets from node-builder
COPY --from=node-builder /app/public/build public/build

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 8080

CMD ["sh", "-lc", "php -S 0.0.0.0:${PORT:-8080} -t public"]
