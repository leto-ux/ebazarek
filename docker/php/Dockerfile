# FROM nginx
# COPY ./website /usr/share/nginx/html

FROM php:8.2-fpm

# Instalacja zależności systemowych i rozszerzeń PHP
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    curl \
    libzip-dev \
    libsqlite3-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalacja composera
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/website

RUN mkdir -p /var/www/website/config

RUN mkdir -p /var/www/website/bin

COPY website/config/litecoin.conf /var/www/website/config/

COPY website/bin/ltx/ /var/www/website/bin

RUN chmod a+x /var/www/website/bin/ltx
