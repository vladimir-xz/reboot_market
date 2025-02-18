FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev
RUN docker-php-ext-install pdo pdo_pgsql zip
RUN curl -sS https://get.symfony.com/cli/installer | bash


RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY . .

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-scripts --no-interaction

EXPOSE 8000
RUN php bin/console tailwind:init
RUN php bin/console tailwind:build

CMD ["bash", "-c", "php bin/console doctrine:migrations:m$PORTigrate --env=prod && php bin/console doctrine:fixtures:load --no-interaction && pwd && ls && php -S 0.0.0.0:$PORT -t public/ index.php"]