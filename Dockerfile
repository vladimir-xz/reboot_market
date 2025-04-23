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

RUN apk --no-cache add pcre-dev ${PHPIZE_DEPS} \ 
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del pcre-dev ${PHPIZE_DEPS}

RUN pecl install redis

RUN composer install --working-dir=/var/www/html


RUN php bin/console tailwind:init
RUN php bin/console tailwind:build
RUN php bin/console asset-map:compile

CMD ["/start.sh"]