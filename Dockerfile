FROM php:8.2-apache

RUN a2enmod rewrite

COPY public/ /var/www/html/

ENV PORT=10000
RUN sed -i "s/80/${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE ${PORT}

CMD ["apache2-foreground"]
