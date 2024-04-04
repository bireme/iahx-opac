default: build

COMPOSE_FILE = docker-compose.yml
COMPOSE_FILE_DEV = .devcontainer/docker-compose-dev.yml

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


## docker-compose shortcuts
build:
	@docker compose -f $(COMPOSE_FILE) build

build_no_cache:
	@docker compose -f $(COMPOSE_FILE) build --no-cache

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




