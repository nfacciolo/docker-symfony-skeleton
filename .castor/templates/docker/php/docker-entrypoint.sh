#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then

  if [ ! -f vendor/autoload.php ]; then
    echo ""
    echo "Aucune application Symfony détectée."
    printf "Créer un nouveau projet Symfony ? [o/N] "
    read -r answer
    case "$answer" in
      [oO]|[oO][uU][iI])
        composer create-project symfony/skeleton:"8.0.x" ./tmp --prefer-dist --no-progress --no-interaction
        cd tmp
        rm -rf var
        cp -R . ..
        cd -
        rm -Rf tmp/

        if [ -f .gitignore.dist ]; then
          cp .gitignore.dist .gitignore
        fi
        ;;
      *)
        echo "Installation annulée."
        ;;
    esac
  fi

	bin/console cache:clear
fi

exec docker-php-entrypoint "$@"
