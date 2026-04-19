#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then

  if [ ! -f vendor/autoload.php ] && [ "${SYMFONY_INIT:-0}" = "1" ]; then
    composer create-project symfony/skeleton:"8.0.x" ./tmp --prefer-dist --no-progress --no-interaction
    cd tmp
    rm -rf var
    cp -R . ..
    cd -
    rm -Rf tmp/

    if [ -f .gitignore.dist ]; then
      cp .gitignore.dist .gitignore
    fi
  fi

  if [ -f vendor/autoload.php ]; then
    bin/console cache:clear
  fi
fi

exec docker-php-entrypoint "$@"
