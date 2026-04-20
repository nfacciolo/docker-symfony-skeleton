ARG PHP_VERSION=8.5
ARG ALPINE_VERSION=3.23
ARG COMPOSER_VERSION=2.9
ARG PHP_EXTENSION_INSTALLER_VERSION=latest

FROM composer:${COMPOSER_VERSION} AS composer

FROM mlocati/php-extension-installer:${PHP_EXTENSION_INSTALLER_VERSION} AS php_extension_installer

# ─── Base: PHP runtime, extensions, Composer ──────────────────────────────────
FROM php:${PHP_VERSION}-fpm-alpine${ALPINE_VERSION} AS base

RUN apk add --no-cache \
        acl \
        file \
        gettext \
        patch \
        unzip \
    ;

COPY --from=php_extension_installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions apcu exif gd intl pdo_pgsql pdo_mysql opcache zip

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY docker/php/prod/php.ini $PHP_INI_DIR/php.ini

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux; \
    composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

RUN addgroup -g 1000 symfony \
    && adduser -u 1000 -G symfony -h /home/symfony -s /bin/sh -D symfony

WORKDIR /srv/app
RUN chown symfony:symfony /srv/app
ENV PHP_DATE_TIMEZONE='Europe/Paris'

# ─── Development ──────────────────────────────────────────────────────────────
FROM base AS app_php_dev

ENV APP_ENV=dev

COPY docker/php/dev/xdebug.ini $PHP_INI_DIR/conf.d/xdebug.ini

RUN apk add --update linux-headers bash \
    && apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-3.5.0 \
    && docker-php-ext-enable xdebug

RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

RUN mkdir -p var/cache var/log && chown -R symfony:symfony var

VOLUME /srv/app/public/media

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
