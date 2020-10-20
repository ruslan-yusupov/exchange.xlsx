# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/
# https://docs.docker.com/compose/compose-file/#target



### NGINX stage
FROM nginx:latest as nginx


### WORKSPACE stage
FROM php:7.4-fpm as php

RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y \
            wget \
            curl \
            git \
            libfreetype6-dev \
            libjpeg62-turbo-dev \
            libpng-dev \
            libzip-dev \
            zlib1g-dev \
            unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

COPY /docker/php/php.ini /usr/local/etc/php/conf.d/
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN mkdir -p /home/www-data/task.local

WORKDIR /home/www-data/task.local

ARG USER_UID
RUN deluser www-data \
    && addgroup --gid $USER_UID www-data \
    && adduser --uid $USER_UID --gid $USER_UID --home /home/www-data --disabled-password --gecos "First Last,RoomNumber,WorkPhone,HomePhone" www-data

COPY composer.* ./

RUN chown -R www-data:www-data ./

USER www-data

#RUN composer clear-cache && composer install