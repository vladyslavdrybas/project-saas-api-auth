#ARG PHP_VERSION=8.1-fpm-alpine3.16
ARG PHP_VERSION=8.2-fpm-alpine3.19
ARG POSTGRES_VERSION=15-alpine
ARG NGINX_VERSION=1.22-alpine

### ### ###
FROM php:${PHP_VERSION} AS php_base

#RUN echo "UTC" > /etc/timezone

RUN apk add --no-cache \
		acl \
		file \
		gettext \
	    bash \
	    gcc \
		g++ \
		icu-dev \
	    autoconf

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions pgsql pdo_pgsql

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --from=composer:2.6.5 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/php.ini

EXPOSE 9000

WORKDIR /app

### ### ###
FROM nginx:${NGINX_VERSION} AS nginx_server

WORKDIR /app

CMD ["nginx"]

EXPOSE 80

### ### ###
FROM postgres:${POSTGRES_VERSION} AS db_server
