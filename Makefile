default: build

COMPOSE_FILE_DEV = .devcontainer/docker-compose-dev.yml

IMAGE_NAME=bireme/iahx-opac
export APP_VER?=$(shell git describe --tags --long --always | sed 's/-g[a-z0-9]\{7\}//' | sed 's/-/\./')
TAG_LATEST=$(IMAGE_NAME):latest

## variable used in docker-compose for tag the build image
export IMAGE_TAG=$(IMAGE_NAME):$(APP_VER)

tag:
	@echo "IMAGE TAG:" $(IMAGE_TAG)

## dev shortcuts
dev_build:
	@docker compose -f $(COMPOSE_FILE_DEV) build

dev_build_no_cache:
	@docker compose -f $(COMPOSE_FILE_DEV) build --no-cache

dev_run:
	@docker compose -f $(COMPOSE_FILE_DEV) up

dev_run_no_cache:
	@docker compose -f $(COMPOSE_FILE_DEV) up iahx_opac

dev_start:
	@docker compose -f $(COMPOSE_FILE_DEV) up -d

dev_rm:
	@docker compose -f $(COMPOSE_FILE_DEV) rm -f

dev_logs:
	@docker compose -f $(COMPOSE_FILE_DEV) logs -f

dev_stop:
	@docker compose -f $(COMPOSE_FILE_DEV) stop

dev_ps:
	@docker compose -f $(COMPOSE_FILE_DEV) ps

dev_sh:
	@docker compose -f $(COMPOSE_FILE_DEV) exec iahx_opac bash

dev_sh_cache:
	@docker compose -f $(COMPOSE_FILE_DEV) exec iahx_cache sh

dev_clear_app_cache:
	@docker compose -f $(COMPOSE_FILE_DEV) exec iahx_opac php bin/console cache:clear

dev_clear_cache:
	@docker compose -f $(COMPOSE_FILE_DEV) exec iahx_opac php bin/console cache:pool:clear cache.global_clearer


## prod/test shortcuts
build:
	@docker compose build --build-arg DOCKER_TAG=$(APP_VER)
	@docker tag $(IMAGE_TAG) $(TAG_LATEST)

build_no_cache:
	@docker compose build --no-cache --build-arg DOCKER_TAG=$(APP_VER)
	@docker tag $(IMAGE_TAG) $(TAG_LATEST)

build_update_php:
	@docker compose build --pull --no-cache --build-arg DOCKER_TAG=$(APP_VER)
	@docker tag $(IMAGE_TAG) $(TAG_LATEST)

run:
	@docker compose down
	@docker compose -f docker-compose.yml up

start:
	@docker compose -f docker-compose.yml up -d

rm:
	@docker compose rm -f

logs:
	@docker compose logs -f

stop:
	@docker compose stop

ps:
	@docker compose ps

sh:
	@docker compose exec iahx_opac bash

sh_cache:
	@docker compose exec iahx_cache sh

sh_webserver:
	@docker compose exec iahx_webserver sh

update_static:
	@docker compose exec iahx_opac php bin/console asset-map:compile

update_env:
	@docker compose exec iahx_opac composer dump-env prod

update_packages:
	@docker compose exec iahx_opac composer install --no-dev --optimize-autoloader

clear_app_cache:
	@docker compose exec iahx_opac php bin/console cache:clear
	@docker compose exec iahx_opac /bin/sh -c 'chmod -R o+w /app/var/cache/'

clear_cache:
	@docker compose exec iahx_opac php bin/console cache:pool:clear cache.global_clearer

# Target to switch to a custom version (use make rollback VERSION=9.9.9)
rollback:
	@echo "Deploying version $$VERSION..."
	@docker compose stop
	@IMAGE_TAG=$(IMAGE_NAME):$(VERSION) docker compose up -d