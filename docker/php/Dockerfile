FROM php:8.5-fpm-alpine

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
        pdo_mysql \
        zip; \
    apk del --no-cache .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Dart Sass, the newest release (the Sass bundle would download its own, older, pinned version when `sass` is not in
# the PATH). The musl build is for Alpine.
RUN set -eux; \
    case "$(uname -m)" in \
        x86_64) sass_arch=x64 ;; \
        aarch64) sass_arch=arm64 ;; \
        *) echo "Unsupported architecture: $(uname -m)" >&2; exit 1 ;; \
    esac; \
    sass_version="$(curl -fsSLI -o /dev/null -w '%{url_effective}' https://github.com/sass/dart-sass/releases/latest | sed 's#.*/##')"; \
    curl -fsSL "https://github.com/sass/dart-sass/releases/download/${sass_version}/dart-sass-${sass_version}-linux-${sass_arch}-musl.tar.gz" \
        | tar -xz -C /opt; \
    ln -s /opt/dart-sass/sass /usr/local/bin/sass; \
    sass --version

WORKDIR /var/www/html
