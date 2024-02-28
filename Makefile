#!make
include .env
include .env.local

echo-env:
	echo ${HOST}
	echo ${APP_ENV}
	echo ${APP_NAME}
	echo ${PROXY_NETWORK}
	echo ${APP_NETWORK}
app-run:
	make env-build
	make echo-env
	docker network ls|grep ${PROXY_NETWORK} > /dev/null || docker network create ${PROXY_NETWORK}
	docker compose -f docker-compose.yml up -d --remove-orphans
app-run-local:
	make app-run
app-check-config:
	make echo-env
	docker compose -f docker-compose.yml config
app-stop:
	docker compose -f docker-compose.yml down
env-build:
	./env-builder.sh