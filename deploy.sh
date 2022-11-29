composer update
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
supervisorctl restart all