FROM php:8.4-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql     && docker-php-ext-enable opcache

RUN install -o www-data -g www-data -d /var/www/upload/image/

RUN {   echo 'upload_max_filesize=5M';   echo 'post_max_size=5M'; } > /usr/local/etc/php/conf.d/uploads.ini

RUN apk add --no-cache autoconf build-base \
    && yes '' | pecl install redis \
    && docker-php-ext-enable redis

WORKDIR /var/www/public
