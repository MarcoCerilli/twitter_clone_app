# --- Stage 1: Installa le dipendenze con Composer ---
# Usiamo un'immagine ufficiale di Composer per questo passaggio
FROM composer:2 as vendor

WORKDIR /app

# Copiamo solo i file necessari per installare le dipendenze
# Questo ottimizza la cache di Docker
COPY composer.json composer.lock ./
# Installiamo solo le dipendenze di produzione, in modo ottimizzato
RUN composer install --no-dev --no-interaction --optimize-autoloader


# --- Stage 2: Crea l'immagine finale di produzione ---
# Partiamo da un'immagine ufficiale che include PHP e il server web Apache
FROM php:8.3-apache

# Installiamo le estensioni PHP necessarie a Symfony per parlare con PostgreSQL
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

# Copiamo tutto il codice della nostra applicazione
WORKDIR /var/www/html
COPY . .
# Copiamo la cartella vendor/ già pronta dallo stage precedente
COPY --from=vendor /app/vendor/ ./vendor/

# Impostiamo i permessi corretti per le cartelle di cache e log di Symfony
RUN chown -R www-data:www-data var
