#!/bin/bash

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
### FRONT-END
npm config set cache /var/www/.npm-cache --global
cd /var/www/frontend && npm install && cd ..

### BACK-END
# shellcheck disable=SC2164
cd backend
if [ ! -f ".env" ]; then
  cp .env.example .env
  chown www-data:www-data .env
fi

if [ ! -f ".env.testing" ]; then
  cp .env.testing.example .env.testing
  chown www-data:www-data .env.testing
fi

composer install
php artisan key:generate
php artisan migrate

chown www-data:1000 vendor
chown www-data:1000 storage
chown www-data:1000 tests
chmod -R 755 storage

php-fpm
