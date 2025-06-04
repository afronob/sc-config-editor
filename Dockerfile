# Dockerfile for Star Citizen Keybind Editor
FROM php:8.2-apache AS builder

# Install necessary build dependencies
RUN apt-get update && \
    apt-get install -y \
        libxml2-dev \
    && docker-php-ext-install xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

FROM php:8.2-apache

# Copy built extensions from builder
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Install required runtime packages
RUN apt-get update && \
    apt-get install -y \
        python3-minimal \
        libxml2 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite headers

# Configure Apache with security headers
RUN echo 'Header set X-Content-Type-Options "nosniff"' >> /etc/apache2/conf-enabled/security.conf \
    && echo 'Header set X-Frame-Options "SAMEORIGIN"' >> /etc/apache2/conf-enabled/security.conf \
    && echo 'Header set X-XSS-Protection "1; mode=block"' >> /etc/apache2/conf-enabled/security.conf

# Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i \
    -e 's/max_execution_time = 30/max_execution_time = 60/' \
    -e 's/memory_limit = 128M/memory_limit = 256M/' \
    -e 's/max_input_vars = 1000/max_input_vars = 5000/' \
    "$PHP_INI_DIR/php.ini"

# Set working directory
WORKDIR /var/www/html

# Copy Apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY bootstrap.php config.php keybind_editor.php favicon.ico ./
COPY assets/ ./assets/
COPY files/ ./files/
COPY src/ ./src/
COPY templates/ ./templates/
COPY external/ ./external/

# Add custom php.ini configurations
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Create required directories and set permissions
RUN mkdir -p /var/www/html/files \
    && chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod -R 775 /var/www/html/files

# Add healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s \
  CMD curl -f http://localhost/ || exit 1

# Expose port 80
EXPOSE 80

# Set Apache as foreground process
CMD ["apache2-foreground"]

# sudo docker build -t sc-keybind-editor . && sudo docker run -p 8080:80 sc-keybind-editor

# # Arrêter le conteneur actuel
# sudo docker stop sc-keybind

# # Supprimer le conteneur
# sudo docker rm sc-keybind

# # Reconstruire l'image
# sudo docker build -t sc-keybind-editor .

# # Relancer le conteneur
# sudo docker run -d --name sc-keybind -p 8080:80 -v $(pwd):/var/www/html sc-keybind-editor