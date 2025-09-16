# FROM php:8.2.1-fpm

# RUN apt-get -y update && apt-get upgrade -y
# RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo_mysql

# RUN pecl install xdebug-3.2.0 && docker-php-ext-enable xdebug

# RUN docker-php-ext-configure pcntl --enable-pcntl \
#   && docker-php-ext-install \
#   pcntl

# CMD ["php-fpm"]


FROM php:8.3-fpm

# установка рабочей директории
WORKDIR /var/www/html

# установка зависимостей дял базы данных
RUN docker-php-ext-install pdo pdo_mysql

# установка supervisor
RUN apt-get update && apt-get install -y supervisor

# установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer




CMD ["php-fpm"]
