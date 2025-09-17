# FROM php:8.2.1-fpm

# RUN apt-get -y update && apt-get upgrade -y
# RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo_mysql

# RUN pecl install xdebug-3.2.0 && docker-php-ext-enable xdebug

# RUN docker-php-ext-configure pcntl --enable-pcntl \
#   && docker-php-ext-install \
#   pcntl

# CMD ["php-fpm"]

# ===

# FROM php:8.3-fpm

# # установка рабочей директории
# WORKDIR /var/www/html

# # установка зависимостей дял базы данных
# RUN docker-php-ext-install pdo pdo_mysql pcntl

# # установка supervisor
# RUN apt-get update && apt-get install -y \
#     supervisor \
#     git \
#     unzip




    
# # установка Composer
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer



# EXPOSE 9000
# CMD ["php-fpm"]

# ===
FROM php:8.3-fpm

# RUN apt-get update && apt-get install -y libpq-dev libpng-dev libzip-dev zip unzip curl
# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor

# RUN docker-php-ext-install pdo pdo_mysql gd zip\
#   && docker-php-ext-enable opcache

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install redis
# RUN pecl install -o -f redis \
#   &&  rm -rf /tmp/pear \
#   &&  docker-php-ext-enable redis
RUN pecl install redis && docker-php-ext-enable redis

# Install Supervisor
RUN apt-get install -y supervisor

RUN mkdir -p "/etc/supervisor/logs"

CMD ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisor/supervisord.conf"]