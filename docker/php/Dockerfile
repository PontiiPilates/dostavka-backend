# FROM php:8.2.1-fpm

# RUN apt-get -y update && apt-get upgrade -y
# RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo_mysql

# RUN pecl install xdebug-3.2.0 && docker-php-ext-enable xdebug

# RUN docker-php-ext-configure pcntl --enable-pcntl \
#   && docker-php-ext-install \
#   pcntl

# CMD ["php-fpm"]


FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN docker-php-ext-install pdo pdo_mysql