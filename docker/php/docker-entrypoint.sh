#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then

  if [ ! -f composer.json ]; then
    composer create-project symfony/skeleton:"8.0.x" ./tmp  --prefer-dist --no-progress --no-interaction
    cd tmp
    rm -rf var
    cp -R . ..
    cd -
    rm -Rf tmp/

    # Restore original .gitignore
    if [ -f .gitignore.dist ]; then
      cp .gitignore.dist .gitignore
    fi

  fi

	bin/console cache:clear
fi

exec docker-php-entrypoint "$@"
