#!/bin/bash
cd /sites/lairgg-services
sudo rm -rf error_log
sudo rm -rf access_log
sudo composer install -n && sudo composer dump-autoload -n
sudo chmod -R 0777 storage bootstrap/cache
sudo php artisan storage:link
sudo chown nginx:nginx /sites -R
sudo php artisan cache:clear
sudo php artisan view:clear
sudo php artisan route:cache
sudo php artisan event:cache
sudo php artisan config:cache
sudo php artisan migrate --force
sudo chmod 600 storage/oauth-private.key
