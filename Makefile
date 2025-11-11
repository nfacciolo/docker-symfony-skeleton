.PHONY: run

PROJECT_NAME = my-custom-project-name
DATABASE_USERNAME = secret
DATABASE_PASSWORD = secret
DATABASE_NAME = secret


DOCKER_COMPOSE ?= PROJECT_NAME=$(PROJECT_NAME) DATABASE_USERNAME=$(DATABASE_USERNAME) DATABASE_PASSWORD=$(DATABASE_PASSWORD) DATABASE_NAME=$(DATABASE_NAME) docker compose
DOCKER_USER ?= "$(shell id -u):$(shell id -g)"
ENV ?= "dev"

init:
	@make -s docker-compose-check
	@if [ ! -e compose.override.yml ]; then \
		cp compose.override.dist.yml compose.override.yml; \
	fi
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) run --rm php composer install --no-interaction --no-scripts
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) run --rm nodejs
	@make -s install
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) up -d

chown:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) run app chown -R 1000:www-data .

chmod:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) run app chmod -R 755 .

## Security: Apply recommended permissions
permissions:
	@echo "Applying secure file permissions..."
	@find . -type f ! -path "./vendor/*" ! -path "./node_modules/*" ! -path "./var/*" ! -path "./.git/*" -exec chmod 644 {} \; || true
	@find . -type d ! -path "./vendor/*" ! -path "./node_modules/*" ! -path "./var/*" ! -path "./.git/*" -exec chmod 755 {} \; || true
	@chmod +x bin/console || true
	@chmod -R 775 var/ || true
	@[ -d public/uploads ] && chmod -R 775 public/uploads/ || true
	@[ -f .env ] && chmod 640 .env || true
	@[ -f .env.local ] && chmod 600 .env.local || true
	@chmod -R g+s var/ || true
	@find . ! -path "./vendor/*" ! -path "./node_modules/*" ! -path "./.git/*" -exec chown $(shell id -u):www-data {} \; 2>/dev/null || true
	@chown -R $(shell id -u):$(shell id -g) .git/ 2>/dev/null || true
	@echo "Permissions applied successfully!"

run:
	@make -s up

debug:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) -f compose.yml -f compose.override.yml -f compose.debug.yml up -d

build:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) build

up:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) up

down:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) down

install:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) run --rm php bin/console sylius:install -s default -n

clean:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) down -v

sh:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app sh

cc:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console c:c

## Migrations commands
mm:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console make:migration

m:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console d:m:m

mp:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console d:m:m prev

mn:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console d:m:m next

## Debug commands
dr:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console debug:router

## Tests commands
t:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app php vendor/bin/phpunit
to:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app php vendor/bin/phpunit tests/OrchardManagement
toc:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app php vendor/bin/phpunit tests/OrchardManagement/Unit/Domain/Entity/OrchardTest.php
tp:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app php vendor/bin/phpunit tests/OrchardManagement/Unit/Domain/Entity/

ns:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) run --rm -i node sh

nw:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) run --rm -i node "npm run watch"

docker-compose-check:
	@$(DOCKER_COMPOSE) version >/dev/null 2>&1 || (echo "Please install docker compose binary or set DOCKER_COMPOSE=\"docker-compose\" for legacy binary" && exit 1)
	@echo "You are using \"$(DOCKER_COMPOSE)\" binary"
	@echo "Current version is \"$$($(DOCKER_COMPOSE) version)\""





## Fixtures commands
f:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console doctrine:fixtures:load --append

faa:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console doctrine:fixtures:load --group=admin --append

fac:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console doctrine:fixtures:load --group=channel --append

fad:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console doctrine:fixtures:load --group=default --append

ffr:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console doctrine:fixtures:load --group=fruits --append

fp:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console doctrine:fixtures:load

fc:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console dbal:run-sql "TRUNCATE TABLE coop_person, coop_orchard RESTART IDENTITY CASCADE"

addNico:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console app:add-person nicolas facciolo facciolo.nicolas@gmail.com

addON:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console app:add-orchard 26

lp:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) $(DOCKER_COMPOSE) exec app bin/console app:list-person



