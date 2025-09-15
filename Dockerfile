FROM php:8.2-fpm

# Install Composer dan dependencies PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set direktori kerja
WORKDIR /var/www/html

# Exposed port
EXPOSE 9000