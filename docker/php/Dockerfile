FROM php:8.3-fpm

# System deps + netcat for DB wait + PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    netcat-openbsd \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
    && rm -rf /var/lib/apt/lists/*

# PHP-FPM pool config
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm", "-F"]