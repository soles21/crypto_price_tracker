FROM php:8.2-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN docker-php-ext-install pdo_sqlite

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# Create startup script
COPY ./docker/startup.sh /startup.sh
RUN chmod +x /startup.sh

# Copy supervisor configuration
COPY ./docker/supervisor/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

# Copy application code
COPY . /var/www

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Install npm dependencies and build assets
RUN npm install && npm run build

# Set permissions
RUN chown -R $user:$user /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache \
    && mkdir -p /var/log/supervisor \
    && touch /var/log/supervisor/supervisord.log \
    && chmod -R 777 /var/log/supervisor

# Create database directory and file
RUN mkdir -p /var/www/database \
    && touch /var/www/database/database.sqlite \
    && chmod 777 /var/www/database/database.sqlite

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM server by default (can be overridden via docker-compose command)
CMD ["php-fpm"]