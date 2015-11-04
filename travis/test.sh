#!/bin/sh

if [ $1 ]; then
	phpunit --configuration travis/phpunit.basic.xml --filter $1
else
	phpunit --configuration travis/phpunit.basic.xml
fi
