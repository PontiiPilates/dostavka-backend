#!/bin/bash

env=$1

if [ "$env" == 'local' ]; then

    echo "Установка прав..."
    chmod -R 777 storage/

else

    echo "Установка прав..."
    chmod -R 775 storage/ bootstrap/cache/
    chown -R www-data:www-data storage/ bootstrap/cache/

fi

echo "Добавление начальных данных..."
unzip -o resources/assets.zip -d ./

echo "Очистка базы данных..."
php artisan db:wipe

echo "Засев базы данных..."
php artisan migrate:refresh --seed