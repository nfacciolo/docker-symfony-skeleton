# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/compose/compose-file/#target

ARG PHP_VERSION=8.5
ARG ALPINE_VERSION=3.22
ARG COMPOSER_VERSION=2.8
ARG PHP_EXTENSION_INSTALLER_VERSION=latest

FROM composer:${COMPOSER_VERSION} AS composer

FROM mlocati/php-extension-installer:${PHP_EXTENSION_INSTALLER_VERSION} AS php_extension_installer

# ─── Base: PHP runtime, extensions, Composer (shared between prod and dev) ────
FROM php:${PHP_VERSION}-fpm-alpine${ALPINE_VERSION} AS base

# persistent / runtime deps
RUN apk add --no-cache \
        acl \
        file \
        gettext \
        patch \
        unzip \
    ;

COPY --from=php_extension_installer /usr/bin/install-php-extensions /usr/local/bin/

# default PHP image extensions
# ctype curl date dom fileinfo filter ftp hash iconv json libxml mbstring mysqlnd openssl pcre PDO pdo_sqlite Phar
# posix readline Reflection session SimpleXML sodium SPL sqlite3 standard tokenizer xml xmlreader xmlwriter zlib
RUN install-php-extensions apcu exif gd intl pdo_pgsql pdo_mysql opcache zip

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY docker/php/prod/php.ini $PHP_INI_DIR/php.ini

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux; \
    composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/app
ENV PHP_DATE_TIMEZONE='Europe/Paris'

# ─── Composer: install PHP deps (shared by node_builder and sylius_php_prod) ──
FROM base AS composer_prod

COPY scripts scripts/
COPY patches patches/
COPY composer.* symfony.lock ./
COPY bundle ./bundle
RUN set -eux; \
    composer install --prefer-dist --no-autoloader --no-interaction --no-scripts --no-progress --no-dev; \
    composer clear-cache

RUN patch -p1 -d vendor/sylius/resource-bundle \
    < patches/sylius-resource-operation-defaults-preserve-class.patch

# ─── Node: compile frontend assets ───────────────────────────────────────────
FROM node:lts-alpine AS node_builder

WORKDIR /srv/app

COPY package.json tsconfig.json webpack.config.js ./
COPY assets assets/
COPY bundle bundle/
COPY --from=composer_prod /srv/app/vendor vendor/

RUN npm install && npm run build:prod

# ─── Production ───────────────────────────────────────────────────────────────
FROM base AS sylius_php_prod

# copy file required by opcache preloading
COPY config/preload.php /srv/app/config/preload.php

# build for production
ENV APP_ENV=prod

COPY --from=composer_prod /srv/app/vendor vendor/
COPY composer.* symfony.lock ./
COPY bundle bundle/

# copy only specifically what we need
COPY .env .env.prod ./
COPY assets assets/
COPY bin bin/
COPY config config/
COPY public public/
COPY src src/
COPY templates templates/
COPY translations translations/
COPY migrations migrations/

COPY --from=node_builder /srv/app/public/build public/build/

RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative; \
    APP_SECRET='' composer run-script post-install-cmd; \
    chmod +x bin/console; sync; \
    bin/console sylius:install:assets --no-interaction; \
    bin/console sylius:theme:assets:install public --no-interaction

VOLUME /srv/app/var
VOLUME /srv/app/public/media

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

# ─── Development (independent from production) ────────────────────────────────
FROM base AS app_php_dev

ENV APP_ENV=dev

COPY scripts scripts/
COPY patches patches/
COPY .env .env.test .env.test_cached ./
COPY composer.* symfony.lock ./
COPY bundle ./bundle
RUN set -eux; \
    composer install --prefer-dist --no-autoloader --no-interaction --no-scripts --no-progress; \
    composer clear-cache

RUN patch -p1 -d vendor/sylius/resource-bundle \
    < patches/sylius-resource-operation-defaults-preserve-class.patch

COPY assets assets/
COPY bin bin/
COPY config config/
COPY public public/
COPY src src/
COPY templates templates/
COPY translations translations/
COPY migrations migrations/

RUN set -eux; \
    mkdir -p var/cache var/log; \
    chmod +x bin/console; \
    sync

COPY docker/php/dev/xdebug.ini $PHP_INI_DIR/conf.d/xdebug.ini

RUN apk add --update linux-headers bash \
    && apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-3.5.0 \
    && docker-php-ext-enable xdebug

RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

VOLUME /srv/app/var
VOLUME /srv/app/public/media

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
