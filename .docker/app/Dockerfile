FROM php:8.4-fpm-alpine

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    curl \
    gd \
    intl \
    mbstring \
    pdo_mysql \
    redis \
    xml \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY ./ /var/www/

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

ENV APP_TIMEZONE=Europe/Berlin

RUN ln -snf /usr/share/zoneinfo/$APP_TIMEZONE /etc/localtime && echo APP_TIMEZONE > /etc/timezone

COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["php-fpm"]


