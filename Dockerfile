ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-alpine
RUN apk add --no-cache autoconf g++ linux-headers make && \
    pecl install -f xdebug && \
    docker-php-ext-enable xdebug && \
    apk del --purge autoconf g++ linux-headers make
