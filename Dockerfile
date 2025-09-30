# --- Stage 1: Installa le dipendenze con Composer ---
FROM composer:2 as vendor

WORKDIR /app

# PRIMA copiamo TUTTI i file dell'applicazione
COPY . .

# ORA lanciamo composer install, che troverà il file bin/console
RUN composer install --no-dev --no-interaction --optimize-autoloader


# --- Stage 2: Crea l'immagine finale di produzione ---
FROM php:8.3-apache

# Installiamo le estensioni PHP necessarie
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl zip pdo pdo_pgsql

# Configuriamo Apache per puntare alla cartella /public di Symfony
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Copiamo l'applicazione GIA PRONTA (con la cartella vendor inclusa) dallo stage precedente
WORKDIR /var/www/html
COPY --from=vendor /app .

# Impostiamo i permessi corretti
RUN chown -R www-data:www-data var
