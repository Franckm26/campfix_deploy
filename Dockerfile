# Use PHP 8.2 with Apache
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Create .env file with APP_KEY for build process
RUN cp .env.example .env || echo "APP_KEY=" > .env

# Install PHP dependencies (skip discovery to avoid APP_KEY requirement)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Run post-install scripts separately (after .env is ready)
RUN php artisan package:discover --ansi || true

# Install Node dependencies and build assets
RUN npm install && npm run build

# Create storage and cache directories with proper permissions
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache

RUN chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8080

# Start the application
CMD php artisan package:discover --ansi && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
