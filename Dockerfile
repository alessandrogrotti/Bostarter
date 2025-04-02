FROM php:8.1-apache

# Installazione delle estensioni richieste
RUN apt-get update && apt-get install -y libssl-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install mongodb \
    && docker-php-ext-enable pdo pdo_mysql mongodb

# Riavvia Apache all'avvio del container
CMD ["apache2-foreground"]
