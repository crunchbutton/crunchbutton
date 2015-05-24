#!/usr/bin/env bash

PHPPATH=$1/.heroku/php/bin/php

./_build.php crunchbutton.com $PHPPATH | tr -d "\t\n\r" > ../www/build/crunchbutton.html
./_build.php cockpit.la $PHPPATH | tr -d "\t\n\r" > ../www/build/cockpit.html

echo $PHPPATH
$PHPPATH -r "echo 'test';"


cat ../www/.htaccess ../www/.htaccess.heroku > ../www/.htaccess.move
mv ../www/.htaccess.move ../www/.htaccess
rm -f ../www/.htaccess.heroku