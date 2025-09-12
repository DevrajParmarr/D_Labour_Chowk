FROM dunglas/frankenphp

# Install additional PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 777 /app/public/images \
    && chmod -R 777 /app/public/uploads \
    && chmod -R 777 /app/cache

# Create necessary directories
RUN mkdir -p /app/public/images /app/public/uploads /app/cache

# Expose port 80
EXPOSE 80

# Start FrankenPHP
CMD ["frankenphp", "run"]