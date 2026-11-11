FROM php:8.2-cli-alpine

WORKDIR /app

# Install system dependencies
RUN apk add --no-cache \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite-dev \
    sqlite

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Set permissions
RUN chmod -R 777 storage bootstrap/cache

# Environment & Dependencies
RUN cp .env.example .env && \
    composer install --no-dev --optimize-autoloader --no-interaction && \
    php artisan key:generate --force && \
    touch database/database.sqlite && \
    php artisan migrate:fresh --seed --force

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
