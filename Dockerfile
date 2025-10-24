# Apache + PHP 8.2
FROM php:8.2-apache

# Gerekli paketler: sqlite dev ve pkg-config
RUN apt-get update \
 && apt-get install -y --no-install-recommends libsqlite3-dev pkg-config \
 && docker-php-ext-install pdo_sqlite \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

# Çalışma dizini
WORKDIR /var/www/html

# Uygulama dosyaları
COPY . .

# SQLite klasörü yazılabilir olsun (projedeki klasör adı "database")
RUN chown -R www-data:www-data /var/www/html/database || true \
 && chmod -R 775 /var/www/html/database || true

EXPOSE 80
