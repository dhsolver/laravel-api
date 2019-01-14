#!/bin/bash

cd /home/forge/api.wejunket.com
git pull origin master
composer install --no-interaction --prefer-dist --optimize-autoloader
echo "" | sudo -S service php7.2-fpm reload

if [ -f artisan ]
then
    php artisan migrate --force
fi
