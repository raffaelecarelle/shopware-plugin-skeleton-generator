# Makefile for Insight Core Project
setup: build composer-update

sh: build
	docker compose run --rm php zsh

build:
	docker compose build php

start:
	docker compose up -d php

composer-update: start
	docker compose exec php composer update --ignore-platform-reqs

pre-commit: rector cs-fix phpstan tests

rector: start
	docker compose exec php vendor/bin/rector --ansi

cs-fix: start
	docker compose exec php vendor/bin/php-cs-fixer fix --verbose --ansi

phpstan: start
	docker compose exec php vendor/bin/phpstan analyse --ansi --memory-limit=-1

tests: start
	docker compose exec php vendor/bin/phpunit --colors=always
