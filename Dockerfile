FROM php:7.4-cli-alpine

WORKDIR /opt/certbot-dns-transip

COPY . .
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache \
        certbot \
        libzip-dev \
        zlib-dev \
        unzip \
    && docker-php-ext-install zip \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && composer install --no-dev --no-progress --prefer-dist --optimize-autoloader

VOLUME ["/etc/letsencrypt", "/var/lib/letsencrypt"]

ENTRYPOINT ["certbot"]
CMD ["certonly", "--preferred-challenges=dns", "--manual", "--manual-auth-hook", "bin/auth", "--manual-cleanup-hook", "bin/cleanup"]
