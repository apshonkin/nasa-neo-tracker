SHELL := /bin/bash
.DEFAULT_GOAL := help

# UID/GID уходят в образ, чтобы файлы в смонтированном каталоге принадлежали мне, а не root
export UID := $(shell id -u)
export GID := $(shell id -g)

DC  := docker compose
PHP := $(DC) run --rm --user www-data php

.PHONY: help
help: ## Показать список команд
	@grep -hE '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

.PHONY: init
init: .env build up install migrate ## Первый запуск: образ, контейнеры, зависимости, миграции
	@source .env && echo "Готово: http://localhost:$${APP_PORT:-8899}"

.env:
	cp .env.example .env
	sed -i "s/^COOKIE_VALIDATION_KEY=.*/COOKIE_VALIDATION_KEY=$$(openssl rand -hex 16)/" .env

.PHONY: build
build: ## Собрать образ PHP для разработки
	$(DC) build

.PHONY: build-prod
build-prod: ## Собрать прод-образ (то же, что проверяет CI)
	docker build --target prod -f docker/php/Dockerfile -t neo-tracker/php:prod .

.PHONY: up
up: .env ## Поднять всё в фоне
	$(DC) up -d

.PHONY: down
down: ## Остановить
	$(DC) down

.PHONY: destroy
destroy: ## Остановить и удалить том с базой
	$(DC) down -v

.PHONY: logs
logs: ## Логи контейнеров
	$(DC) logs -f --tail=100

.PHONY: install
install: ## composer install
	$(PHP) composer install

.PHONY: migrate
migrate: ## Применить миграции
	$(PHP) php yii migrate --interactive=0

.PHONY: apod
apod: ## Загрузить картинку дня
	$(PHP) php yii nasa/apod

.PHONY: asteroids
asteroids: ## Загрузить сближения на 7 дней
	$(PHP) php yii nasa/asteroids

.PHONY: sh
sh: ## Шелл в контейнере PHP
	$(PHP) sh

.PHONY: psql
psql: ## psql в базе приложения
	$(DC) exec postgres sh -c 'psql -U "$$POSTGRES_USER" -d "$$POSTGRES_DB"'

.PHONY: redis-cli
redis-cli: ## redis-cli
	$(DC) exec redis redis-cli

.PHONY: test
test: ## Тесты
	$(PHP) composer test

.PHONY: check
check: ## Всё, что гоняет CI: стиль, phpstan, тесты
	$(PHP) composer cs
	$(PHP) composer static
	$(PHP) composer test
