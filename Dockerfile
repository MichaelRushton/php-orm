ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-alpine
COPY --from=ghcr.io/php/pie:bin /pie /usr/bin/pie
RUN apk add --no-cache autoconf g++ linux-headers make && \
    pie install xdebug/xdebug && \
    apk del --purge autoconf g++ linux-headers make
