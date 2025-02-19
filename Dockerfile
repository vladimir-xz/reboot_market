FROM richarvey/nginx-php-fpm:latest

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev
RUN docker-php-ext-install pdo pdo_pgsql zip
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt install symfony-cli

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

WORKDIR /var/www/html

COPY . .

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr
# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-scripts --no-interaction --working-dir=/var/www/html

RUN php bin/console tailwind:init
RUN php bin/console tailwind:build

CMD ["bash", "-c", "php bin/console doctrine:migrations:migrate --env=prod && php bin/console doctrine:fixtures:load --no-interaction && pwd && ls /etc/nginx/conf.d/"]