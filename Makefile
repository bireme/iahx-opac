default: build

COMPOSE_FILE = docker-compose.yml
COMPOSE_FILE_DEV = .devcontainer/docker-compose-dev.yml

IMAGE_NAME=bireme/iahx-opac
export APP_VER?=$(shell git describe --tags --long --always | sed 's/-g[a-z0-9]\{7\}//' | sed 's/-/\./')
TAG_LATEST=$(IMAGE_NAME):latest

## variable used in docker-compose for tag the build image
export IMAGE_TAG=$(IMAGE_NAME):$(APP_VER)

tag:
	@echo "IMAGE TAG:" $(IMAGE_TAG)

## docker-compose shortcuts
dev_build:
	@docker compose -f $(COMPOSE_FILE_DEV) build

dev_build_no_cache:
	@docker compose -f $(COMPOSE_FILE_DEV) build --no-cache

dev_run:
	@docker compose -f $(COMPOSE_FILE_DEV) up

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


## docker-compose shortcuts
build:
	@docker compose -f $(COMPOSE_FILE) build --build-arg DOCKER_TAG=$(APP_VER)
	@docker tag $(IMAGE_TAG) $(TAG_LATEST)

build_no_cache:
	@docker compose -f $(COMPOSE_FILE) build --no-cache --build-arg DOCKER_TAG=$(APP_VER)
	@docker tag $(IMAGE_TAG) $(TAG_LATEST)

run:
	@docker compose -f $(COMPOSE_FILE) down
	@docker compose -f $(COMPOSE_FILE) up

start:
	@docker compose -f $(COMPOSE_FILE) up -d

rm:
	@docker compose -f $(COMPOSE_FILE) rm -f

logs:
	@docker compose -f $(COMPOSE_FILE) logs -f

stop:
	@docker compose -f $(COMPOSE_FILE) stop

ps:
	@docker compose -f $(COMPOSE_FILE) ps

sh:
	@docker compose -f $(COMPOSE_FILE) exec iahx_opac bash

update_static:
	@docker compose -f $(COMPOSE_FILE) exec iahx_opac php bin/console asset-map:compile

update_env:
	@docker compose -f $(COMPOSE_FILE) exec iahx_opac composer dump-env prod

update_packages:
	@docker compose -f $(COMPOSE_FILE) exec iahx_opac composer install --no-dev --optimize-autoloader

clear_cache:
	@docker compose -f $(COMPOSE_FILE) exec iahx_opac php bin/console cache:clear




