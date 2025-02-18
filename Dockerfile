FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev
RUN docker-php-ext-install pdo pdo_pgsql zip


RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

WORKDIR /app

COPY . .

RUN composer install
RUN php bin/console tailwind:init
RUN php bin/console tailwind:build

CMD ["bash", "-c", "php bin/console doctrine:migrations:migrate --env=prod && php bin/console doctrine:fixtures:load --no-interaction && symfony server:start --listen-ip=0.0.0.0 --port=$PORT"]