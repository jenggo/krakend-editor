FROM php:fpm-alpine3.10
RUN apk add zlib-dev libzip-dev && docker-php-ext-install zip

