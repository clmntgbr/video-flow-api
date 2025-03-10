#!/usr/bin/env bash

include .env
export $(shell sed 's/=.*//' .env)

DOCKER_COMPOSE = docker compose -p $(PROJECT_NAME)

CONTAINER_PHP := $(shell docker container ls -f "name=$(PROJECT_NAME)-php" -q)
CONTAINER_QA := $(shell docker container ls -f "name=$(PROJECT_NAME)-qa" -q)
CONTAINER_DB := $(shell docker container ls -f "name=$(PROJECT_NAME)-database" -q)

PHP := docker exec -ti $(CONTAINER_PHP)
DATABASE := docker exec -ti $(CONTAINER_DB)
QA := docker exec -ti $(CONTAINER_QA)

## Kill all containers
kill:
	@$(DOCKER_COMPOSE) kill $(CONTAINER) || true

## Build containers
build:
	@$(DOCKER_COMPOSE) build --pull --no-cache

## Init project
init: install update

## Start containers
start:
	@$(DOCKER_COMPOSE) up -d

## Stop containers
stop:
	@$(DOCKER_COMPOSE) down

restart: stop start

## Init project
init: install update npm fabric db

npm: 
	$(PHP) npm install
	$(PHP) npm run build

cache:
	$(PHP) rm -r var/cache

## Entering shells
php:
	@$(DOCKER_COMPOSE) exec php sh

database:
	@$(DOCKER_COMPOSE) exec database sh

## Composer install
install:
	$(PHP) composer install

update:
	$(PHP) composer update

dotenv:
	$(PHP) php bin/console debug:dotenv

jwt: 
	$(PHP) php bin/console lexik:jwt:generate-keypair --skip-if-exists

fabric: 
	$(PHP) php bin/console messenger:setup-transports

db: 
	$(PHP) php bin/console doctrine:database:drop -f
	$(PHP) php bin/console doctrine:database:create
	$(PHP) php bin/console doctrine:schema:update -f
	$(PHP) php bin/console hautelook:fixtures:load -n

php-cs-fixer:
	$(QA) ./php-cs-fixer fix src --rules=@Symfony --verbose --diff

php-stan:
	$(QA) ./vendor/bin/phpstan analyse src -l $(or $(level), 5)

schema:
	$(PHP) php bin/console doctrine:schema:update -f

regenerate:
	$(PHP) php bin/console make:entity --regenerate App

entity:
	$(PHP) php bin/console make:entity

fixtures:
	$(PHP) php bin/console hautelook:fixtures:load -n

consume-sound-extractor:
	$(PHP) php bin/console messenger:consume sound_extractor_to_api -vv
	
consume-subtitle-generator:
	$(PHP) php bin/console messenger:consume subtitle_generator_to_api -vv
	
consume-subtitle-merger:
	$(PHP) php bin/console messenger:consume subtitle_merger_to_api -vv
	
consume-subtitle-transformer:
	$(PHP) php bin/console messenger:consume subtitle_transformer_to_api -vv
	
consume-subtitle-incrustator:
	$(PHP) php bin/console messenger:consume subtitle_incrustator_to_api -vv
	
consume:
	$(PHP) php bin/console messenger:consume sound_extractor_to_api subtitle_generator_to_api subtitle_merger_to_api subtitle_transformer_to_api subtitle_incrustator_to_api video_formatter_to_api video_splitter_to_api video_incrustator_to_api -vv
