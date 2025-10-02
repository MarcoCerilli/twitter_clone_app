# --- Stage 1: Build delle dipendenze ---
FROM composer:2 as vendor
WORKDIR /app
COPY . .
ARG APP_ENV=prod
# Esegui composer install per scaricare le dipendenze
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# --- Stage 2: Crea l'immagine finale di produzione ---
FROM php:8.3-fpm-alpine

ENV APP_ENV=prod
ENV APP_DEBUG=0

# ==> LA SOLUZIONE È QUESTA RIGA <==
# Copia l'eseguibile di Composer per poterlo usare nel container finale
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installiamo le estensioni PHP necessarie per MariaDB/MySQL
RUN apk add --no-cache \
        libzip-dev \
        libpq-dev \
        icu-dev \
        mariadb-client \
    && docker-php-ext-install intl zip pdo pdo_mysql\
    && rm -rf /var/cache/apk/*

# WORKDIR e COPY rimangono come sono
WORKDIR /var/www/html
COPY --from=vendor /app .

# Impostiamo i permessi corretti per l'utente www-data di Alpine
RUN chown -R www-data:www-data var
