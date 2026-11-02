FROM php:8.4-fpm-alpine3.21

RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        linux-headers \
        oniguruma-dev; \
    apk add --no-cache \
        bash \
        freetype \
        git \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libwebp \
        libzip \
        tzdata \
        unzip; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" \
        exif \
        gd \
        intl \
        opcache \
        pdo_mysql \
        zip; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    apk del --no-cache .build-deps

COPY --from=composer:2.8.5 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html
