#!/usr/bin/env bash

cd /var/app
curl -sS https://getcomposer.org/installer | php
./composer.phar install --no-dev --ignore-platform-reqs
