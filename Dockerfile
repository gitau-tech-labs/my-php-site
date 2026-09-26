FROM php:8.2-apache

# Install PostgreSQL client library + PHP extensions
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite (useful for pretty URLs later)
RUN a2enmod rewrite

# Copy site files to Apache document root
COPY public/ /var/www/html/

# Render requires the app to listen on $PORT (default 10000)
ENV PORT=10000
RUN sed -i "s/80/${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE ${PORT}

CMD ["apache2-foreground"]
