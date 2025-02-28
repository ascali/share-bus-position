# Use official PHP image with Apache
FROM php:8.2-apache

USER root

# Install required extensions, OpenSSL, and Certbot
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    cron \
    certbot \
    python3-certbot-apache \
    openssl \
    ca-certificates \
    && update-ca-certificates \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install mailparse \
    && docker-php-ext-enable mailparse \
    # gd
    && apt-get install -y build-essential nginx openssl libfreetype6-dev libjpeg-dev libpng-dev libwebp-dev zlib1g-dev libzip-dev gcc g++ make vim unzip curl git jpegoptim optipng pngquant gifsicle locales libonig-dev  \
    && docker-php-ext-configure gd  \
    && docker-php-ext-install gd \
    # gmp
    && apt-get install -y --no-install-recommends libgmp-dev \
    && docker-php-ext-install gmp \
    # pdo_mysql
    # && docker-php-ext-install pdo_mysql mbstring \
    && docker-php-ext-install mbstring \
    # pdo
    && docker-php-ext-install pdo \
    # opcache
    && docker-php-ext-enable opcache \
    # exif
    && docker-php-ext-install exif \
    && docker-php-ext-install sockets \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install bcmath \
    # zip
    && docker-php-ext-install zip \
    && apt-get autoclean -y \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /tmp/pear/

# Install Composer
# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ENV COMPOSER_ALLOW_SUPERUSER=1

# Configure Apache
COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite ssl # Enable SSL module
    
RUN mkdir -p /etc/ssl/certs && apt-get install --reinstall ca-certificates && update-ca-certificates

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
# RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 775 /var/www/html/writable && chmod -R 775 /var/www/html/public

# Copy PHP configuration file for SMTP
COPY php.ini /usr/local/etc/php/conf.d/php.ini

# Copy auto-renew script
COPY renew-certs.sh /usr/local/bin/renew-certs.sh
RUN chmod +x /usr/local/bin/renew-certs.sh

# Add cron job for auto-renew
RUN echo "0 0 * * * /usr/local/bin/renew-certs.sh" | crontab -
    