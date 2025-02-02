#!/bin/bash

set -e

DOCKER_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
COMPOSER_DIR=~/.composer
mkdir -p $COMPOSER_DIR

docker build -t application $DOCKER_DIR
docker run --rm -ti \
  -v $DOCKER_DIR/../:/home/application/projects \
  -v $COMPOSER_DIR:/home/application/.composer \
  -u application \
  application zsh