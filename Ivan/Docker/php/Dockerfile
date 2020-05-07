FROM php:7.1-fpm

RUN apt-get update \
 && apt-get install -y \
    libc-client-dev libkrb5-dev libxml2-dev libpng-dev \
    zip unzip libz-dev \
    libmemcached-dev \
    imagemagick libmagickwand-dev \
    wget sudo git \
    libgeoip-dev \
    libfontenc1 xfonts-75dpi xfonts-base xfonts-encodings xfonts-utils \
    --no-install-recommends \
 && rm -rf /var/lib/apt/lists/* \
 && pecl install -f memcached \
 && echo extension=memcached.so >> /usr/local/etc/php/conf.d/docker-php-ext-memcached.ini \
 && docker-php-ext-enable memcached \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install -j$(nproc) mbstring imap soap gd zip pdo_mysql pcntl \
 && pecl install -f imagick \
 && docker-php-ext-enable imagick \
 && pecl install geoip-1.1.1 \
 && docker-php-ext-enable geoip

VOLUME /app/

WORKDIR /app/

EXPOSE 9000 9001
