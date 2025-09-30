# --- Stage 1: Build delle dipendenze ---
FROM composer:2 as vendor
WORKDIR /app
COPY . .
# Passiamo l'argomento APP_ENV per ottimizzare l'autoloader per la produzione
ARG APP_ENV=prod
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# --- Stage 2: Crea l'immagine finale di produzione ---
FROM php:8.3-apache

# Impostiamo le variabili d'ambiente di produzione di Symfony
# Questo dice a Symfony di non cercare il file .env
ENV APP_ENV=prod
ENV APP_DEBUG=0

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

# Copiamo l'applicazione già pronta dallo stage precedente
WORKDIR /var/www/html
COPY --from=vendor /app .

# Impostiamo i permessi corretti
RUN chown -R www-data:www-data var
