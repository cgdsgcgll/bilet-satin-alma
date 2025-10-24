
FROM php:8.2-apache


RUN apt-get update \
 && apt-get install -y --no-install-recommends libsqlite3-dev pkg-config \
 && docker-php-ext-install pdo_sqlite \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*


WORKDIR /var/www/html


COPY . .


RUN chown -R www-data:www-data /var/www/html/database || true \
 && chmod -R 775 /var/www/html/database || true

EXPOSE 80
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

