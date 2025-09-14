# Multi-stage Dockerfile for Laravel app (PHP 8.1)
FROM php:8.1-fpm-bullseye AS base

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

## Builder stage for node assets
FROM node:18-bullseye AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --silent
COPY resources resources
COPY vite.config.js .
RUN npm run build

## Final stage
FROM base AS final
WORKDIR /var/www/html

# copy composer files first to leverage layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Copy application code
COPY . .

# copy built assets from node-builder if exist
COPY --from=node-builder /app/dist public/dist || true

# set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 8080

# Use PHP built-in server for lightweight deployments on Railway.
# Railway provides $PORT environment variable; fallback to 8080 when not set.
CMD ["sh", "-lc", "php -S 0.0.0.0:${PORT:-8080} -t public"]
