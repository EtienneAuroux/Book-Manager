# Use official PHP image with extensions
FROM laravelsail/php83-composer:latest

# Set working directory
WORKDIR /var/www/html

# Copy files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chmod -R 775 storage bootstrap/cache

# Create SQLite DB file
RUN touch /tmp/database.sqlite

# Laravel setup
RUN php artisan migrate --force

# Expose port
EXPOSE 8000

# Start Laravel app
CMD touch /tmp/database.sqlite && php artisan migrate --force && php -S 0.0.0.0:8000 -t public


