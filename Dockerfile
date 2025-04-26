FROM richarvey/nginx-php-fpm:latest 

COPY . .
COPY build/nginx/default.conf /etc/nginx/sites-enabled/


# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV prod
ENV APP_DEBUG true
ENV LOG_CHANNEL stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN apk --no-cache add --virtual .build-deps \
      $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY session.ini /usr/local/etc/php/conf.d/session.ini
COPY build/php-fpm/www.conf  /usr/local/etc/php-fpm.d/www.conf
COPY .env.local ./

RUN composer install --working-dir=/var/www/html


RUN php bin/console tailwind:init
RUN php bin/console tailwind:build
RUN php bin/console asset-map:compile
RUN composer dump-env prod

CMD ["/start.sh"]