FROM php:8.3-fpm-alpine

RUN apk add --no-cache bash icu-dev libzip-dev postgresql-dev $PHPIZE_DEPS

RUN docker-php-ext-configure intl \
 && docker-php-ext-install -j$(nproc) intl opcache pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ARG INSTALL_XDEBUG=0
RUN if [ "$INSTALL_XDEBUG" = "1" ]; then pecl install xdebug && docker-php-ext-enable xdebug; fi

CMD ["php-fpm"]
