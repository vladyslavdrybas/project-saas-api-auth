#!make
include .env

echo-env:
	echo ${HOST}
	echo ${APP_NAME}
	echo ${PROXY_NETWORK}
	echo ${APP_NETWORK}
app-run-local:
	make echo-env
	docker network ls|grep ${PROXY_NETWORK} > /dev/null || docker network create ${PROXY_NETWORK}
	docker compose -f docker-compose.yml -f docker-compose.local.yml up -d --remove-orphans
app-check-config-local:
	make echo-env
	docker compose -f docker-compose.yml -f docker-compose.local.yml config
app-stop-local:
	docker compose -f docker-compose.yml -f docker-compose.local.yml down
