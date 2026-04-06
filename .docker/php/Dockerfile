FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        bash \
        curl \
        git \
        mariadb-client \
        unzip

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
        bcmath \
        curl \
        dom \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        redis \
        xml \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser appuser

RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/vendor && \
    chown -R appuser:appuser /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

ENV COMPOSER_ALLOW_SUPERUSER=0
ENV COMPOSER_HOME=/home/appuser/.composer

USER appuser

EXPOSE 9000

CMD ["php-fpm"]
