FROM php:8.3-cli-alpine
RUN apk add --no-cache postgresql-dev icu-dev git unzip \
    && docker-php-ext-install pdo_pgsql pdo_mysql intl
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /srv/app
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
