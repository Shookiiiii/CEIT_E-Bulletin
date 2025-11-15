# Use official PHP CLI image with Apache (you can also use php:8.1-apache)
FROM php:8.1-apache

# Copy project files to Apache root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Install mysqli extension (needed for MySQL connections)
RUN docker-php-ext-install mysqli

# Set permissions for uploads if needed
RUN chown -R www-data:www-data /var/www/html

# Expose default Apache port
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
