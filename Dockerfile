FROM php:8.1-apache

# Installa le dipendenze necessarie per MongoDB, MySQL, e altre librerie
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    zlib1g-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli

# Installa Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Installa MongoDB PHP extension
RUN pecl install mongodb \
    && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini

# Abilita mod_rewrite per Apache
RUN a2enmod rewrite

# Imposta la cartella di lavoro sulla directory backend
WORKDIR /var/www/html/backend

# Copia il codice backend (se disponibile)
COPY ./backend /var/www/html/backend

# Esegui Composer per installare le dipendenze PHP
RUN composer install --no-interaction --optimize-autoloader

# Imposta i permessi per Apache
RUN chown -R www-data:www-data /var/www/html

# Espone la porta 80
EXPOSE 80

# Esegui Apache in modalità foreground
CMD ["apache2-foreground"]
