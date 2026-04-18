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

    # Restore PROJECT_NAME overwritten by Symfony's .env
    if [ -n "${PROJECT_NAME:-}" ] && ! grep -q "^PROJECT_NAME=" .env 2>/dev/null; then
      printf "\nPROJECT_NAME=%s\n" "$PROJECT_NAME" >> .env
    fi

    # Restore original .gitignore
    if [ -f .gitignore.dist ]; then
      cp .gitignore.dist .gitignore
    fi

    chown -R symfony:symfony /srv/app
  fi

	bin/console cache:clear
fi

exec docker-php-entrypoint "$@"
