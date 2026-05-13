# Use a production-ready image with PHP and Nginx
FROM webdevops/php-nginx:8.2

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git unzip

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy configuration files
COPY nginx.conf /etc/nginx/vhost.conf
COPY run.sh /usr/local/bin/run.sh

# Make the startup script executable
RUN chmod +x /usr/local/bin/run.sh

# Expose port (Render uses 80 by default for web services)
EXPOSE 80

# Use the shell script for startup
ENTRYPOINT ["/usr/local/bin/run.sh"]
