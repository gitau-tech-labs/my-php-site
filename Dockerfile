FROM php:8.2-apache

# Install PostgreSQL dependencies and the PHP pdo_pgsql extension
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

RUN a2enmod rewrite

COPY public/ /var/www/html/

ENV PORT=10000
RUN sed -i "s/80/${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE ${PORT}

CMD ["apache2-foreground"]
