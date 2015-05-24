#!/usr/bin/env bash

PHPPATH=$1/.heroku/php/bin/php

$PHPPATH -f ./_build.php crunchbutton.com $PHPPATH | tr -d "\t\n\r" > ../www/build/crunchbutton.html
$PHPPATH -f ./_build.php cockpit.la $PHPPATH | tr -d "\t\n\r" > ../www/build/cockpit.html


#cat ../www/.htaccess ../www/.htaccess.heroku > ../www/.htaccess.move
#mv ../www/.htaccess.move ../www/.htaccess
#rm -f ../www/.htaccess.heroku