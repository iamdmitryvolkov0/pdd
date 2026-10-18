SHELL := /bin/sh

DOCKER_COMPOSE := docker compose
APP_SERVICE := app

.PHONY: help init build up down restart logs shell migrate import status

help:
	@echo "Доступные команды:"
	@echo "  make init     Собрать и полностью инициализировать приложение"
	@echo "  make up       Запустить контейнеры на http://localhost:8088"
	@echo "  make down     Остановить и удалить контейнеры"
	@echo "  make build    Пересобрать образ приложения"
	@echo "  make restart  Перезапустить контейнеры"
	@echo "  make logs     Следить за логами приложения"
	@echo "  make shell    Открыть shell внутри контейнера"
	@echo "  make migrate  Выполнить миграции"
	@echo "  make import   Повторно импортировать вопросы"
	@echo "  make status   Показать состояние контейнеров"

init:
	$(DOCKER_COMPOSE) build
	$(DOCKER_COMPOSE) up -d --wait --wait-timeout 60
	$(DOCKER_COMPOSE) exec -T $(APP_SERVICE) php artisan migrate --force
	$(DOCKER_COMPOSE) exec -T $(APP_SERVICE) php artisan pdd:import
	@echo "Вектор ПДД запущен: http://localhost:8088"

build:
	$(DOCKER_COMPOSE) build

up:
	$(DOCKER_COMPOSE) up -d --wait --wait-timeout 60
	@echo "Вектор ПДД доступен: http://localhost:8088"

down:
	$(DOCKER_COMPOSE) down

restart: down up

logs:
	$(DOCKER_COMPOSE) logs -f $(APP_SERVICE)

shell:
	$(DOCKER_COMPOSE) exec $(APP_SERVICE) sh

migrate:
	$(DOCKER_COMPOSE) exec -T $(APP_SERVICE) php artisan migrate --force

import:
	$(DOCKER_COMPOSE) exec -T $(APP_SERVICE) php artisan pdd:import

status:
	$(DOCKER_COMPOSE) ps
