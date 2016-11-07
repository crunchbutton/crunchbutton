#!/bin/sh

if [ $1 ]; then
	/Applications/MAMP/bin/php/php5.6.10/bin/php ~/Desktop/phpunit-5.6.2.phar --configuration travis/phpunit.basic.xml --filter $1
else
	/Applications/MAMP/bin/php/php5.6.10/bin/php ~/Desktop/phpunit-5.6.2.phar --configuration travis/phpunit.basic.xml
fi
