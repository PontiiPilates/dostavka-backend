FROM php:8.3-fpm

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

# Install system dependencies
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install redis
RUN pecl install redis && docker-php-ext-enable redis

# Install Supervisor
RUN apt-get install -y supervisor
RUN mkdir -p "/etc/supervisor/logs"

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]