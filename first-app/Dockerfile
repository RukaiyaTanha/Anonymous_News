# Multi-stage Dockerfile: build frontend then run PHP app

# Stage 1: build frontend assets with Node
FROM node:18-alpine AS node_builder
WORKDIR /app
COPY package.json package-lock.json* yarn.lock* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi
COPY . .
RUN npm run build

# Stage 2: run PHP app
FROM php:8.4-fpm-alpine
WORKDIR /app

# Install system dependencies
RUN apk add --no-cache bash git libpng libpng-dev libzip-dev oniguruma-dev sqlite sqlite-dev zlib-dev openssl-dev icu-dev curl

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite pdo_mysql mbstring zip

# Install composer (copy from official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app source and built assets
COPY --from=node_builder /app /app

# Install PHP dependencies
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Ensure sqlite file exists and storage link
RUN php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
RUN php artisan storage:link || true

ENV PORT=8080
EXPOSE 8080

CMD ["sh", "-lc", "php artisan serve --host 0.0.0.0 --port ${PORT}"]
