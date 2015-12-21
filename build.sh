#!/usr/bin/env bash

cd /app
curl -sS https://getcomposer.org/installer | php
./composer.phar install --no-dev --ignore-platform-reqs
