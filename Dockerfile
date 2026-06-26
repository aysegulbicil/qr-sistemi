# ============================================================
#  qr-sistemi - CodeIgniter 4 / PHP 8.2 (Apache) Docker imaji
# ============================================================
FROM php:8.2-apache

# --- Sistem bagimliliklari + PHP eklentileri -----------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libonig-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        mbstring \
        gd \
        zip \
        pdo_mysql \
        mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# --- Composer (resmi imajdan kopyalanir) ---------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# --- Apache: mod_rewrite + docroot = public/ -----------------
RUN a2enmod rewrite headers
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# --- Ozel PHP ayarlari ---------------------------------------
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-qr-sistemi.ini

# --- Giris betigi (composer install + writable izinleri) -----
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint \
    && chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/html

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]
