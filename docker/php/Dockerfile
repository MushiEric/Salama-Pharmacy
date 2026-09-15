FROM php:8.4-cli-alpine

ARG WWWUSER=1000
ARG WWWGROUP=1000

RUN apk add --no-cache \
        bash \
        git \
        unzip \
        libpq \
        oniguruma \
        icu-libs \
        libzip \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        postgresql-dev \
        oniguruma-dev \
        icu-dev \
        libzip-dev \
    && docker-php-ext-install pdo_pgsql pgsql mbstring bcmath intl zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN { \
        echo 'memory_limit=512M'; \
        echo 'upload_max_filesize=20M'; \
        echo 'post_max_size=20M'; \
    } > /usr/local/etc/php/conf.d/99-salamapharma.ini

RUN addgroup -g ${WWWGROUP} sail \
    && adduser -D -u ${WWWUSER} -G sail -s /bin/bash sail

WORKDIR /var/www/html

USER sail

EXPOSE 80

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
