#!/bin/sh
set -e

GREEN=$(tput setaf 2)
PINK=$(tput setaf 5)

echo "${PINK}Building docker images ..."

# Build docker images
docker-compose build

# Spring up docker containers in detached mode
docker-compose up -d --force-recreate

echo "${PINK}Running migrations ..."

docker-compose exec product_app php bin/console doctrine:migrations:migrate

echo "${GREEN} Application dockerized and started!"