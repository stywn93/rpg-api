FROM php:8.4-apache

# Install ekstensi PHP yang dibutuhkan
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    nano \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    mysqli \
    zip

# Enable Apache modules
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Fallback .env dari env.example untuk build di server (clone tanpa .env)
RUN if [ ! -f .env ] && [ -f env.example ]; then cp env.example .env; fi

# Install dependencies
RUN composer install --no-dev --optimize-autoloader
#
## Configure Apache DocumentRoot untuk CodeIgniter 4
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

## Update Apache configuration untuk allow .htaccess
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/codeigniter.conf
#
RUN a2enconf codeigniter

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN chmod -R 777 /var/www/html/writable

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]
