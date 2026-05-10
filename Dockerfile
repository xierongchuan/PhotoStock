FROM php:8.5-cli

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install \
    --no-interaction \
    --no-ansi \
    --no-progress \
    --prefer-dist

RUN mkdir -p \
        storage/app \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-photostock.ini
COPY docker/start.sh /usr/local/bin/start-photostock
RUN chmod +x /usr/local/bin/start-photostock

EXPOSE 8000

CMD ["start-photostock"]
