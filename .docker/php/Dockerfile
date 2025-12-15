FROM php:8.1-fpm-alpine

# Install dependencies and extensions
RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    libxml2-dev \
    icu-dev \
    git \
    msmtp \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo_mysql opcache zip intl soap

# Set working directory
WORKDIR /var/www/html
