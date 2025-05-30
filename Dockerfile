# Dockerfile for Star Citizen Keybind Editor (PHP + Python)
FROM php:8.2-apache

# Install required packages
RUN apt-get update && \
    apt-get install -y python3 python3-pip && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite (optional, for pretty URLs)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy PHP, JS, Python, and XML files
COPY *.php ./
COPY *.js ./
COPY files/ ./files/
COPY templates/ ./templates/

# Add custom php.ini to increase max_input_vars
COPY php.ini /usr/local/etc/php/conf.d/

# Expose port 80
EXPOSE 80

# Set permissions (if needed)
RUN chown -R www-data:www-data /var/www/html

# Start Apache in the foreground
CMD ["apache2-foreground"]

# sudo docker build -t sc-keybind-editor .
# sudo docker run -p 8080:80 sc-keybind-editor

# sudo docker build -t sc-keybind-editor . && sudo docker run -p 8080:80 sc-keybind-editor