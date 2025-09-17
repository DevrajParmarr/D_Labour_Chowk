FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy application code
COPY . .

# Create necessary directories and set proper permissions
RUN mkdir -p /var/www/html/Shared/uploads \
    && mkdir -p /var/www/html/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/Shared/uploads \
    && chmod -R 777 /var/www/html/cache

# Enable Apache modules
RUN a2enmod rewrite headers

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Expose port 80
EXPOSE 80

# Create startup script to configure Apache port
RUN echo '#!/bin/bash\n\
if [ -n "$PORT" ]; then\n\
    echo "Listen $PORT" > /etc/apache2/ports.conf\n\
    echo "Setting Apache to listen on port $PORT"\n\
else\n\
    echo "Listen 80" > /etc/apache2/ports.conf\n\
    echo "Setting Apache to listen on port 80"\n\
fi\n\
apache2-foreground' > /usr/local/bin/start-apache.sh && \
chmod +x /usr/local/bin/start-apache.sh

# Start Apache with port configuration
CMD ["/usr/local/bin/start-apache.sh"]