FROM php:8.3-fpm-alpine

# Imposta il nome del servizio
ENV COMPOSE_PROJECT_NAME my_project

# Installa le dipendenze di sistema e le estensioni PHP
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    git \
    libxml2-dev \
    unzip \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    mariadb-connector-c-dev \
    linux-headers

RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    opcache \
    zip \
    intl \
    mbstring
    
# Installa Xdebug (utile per il debug in sviluppo)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Aggiunge un utente non-root per evitare problemi di permessi
RUN adduser -D -u 1000 app
USER app

# Imposta la directory di lavoro
WORKDIR /var/www/html

# Copia e installa Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Esegue composer install e copia il codice
COPY --chown=app:app . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Esponi la porta
EXPOSE 9000

CMD ["php-fpm"]