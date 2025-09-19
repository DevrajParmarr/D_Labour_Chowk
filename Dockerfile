FROM php:8.2-apache

# Install system dependencies including PostgreSQL client
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    postgresql-client \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mysqli mbstring exif pcntl bcmath gd zip

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

# Configure Apache for Render
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    echo "Listen 80" > /etc/apache2/ports.conf && \
    echo "Listen 10000" >> /etc/apache2/ports.conf

# Create Apache virtual host configuration for dynamic port
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>\n\
<VirtualHost *:10000>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expose ports 80 and 10000
EXPOSE 80 10000

# Create startup script
RUN echo '#!/bin/bash\n\
echo "Starting Apache on ports 80 and 10000"\n\
apache2-foreground' > /usr/local/bin/start-apache.sh && \
chmod +x /usr/local/bin/start-apache.sh

# Start Apache
CMD ["/usr/local/bin/start-apache.sh"]