#!/usr/bin/env bash

PHPPATH=php
cd /app/cli/

$PHPPATH -f ./_build.php crunchbutton.com $PHPPATH | tr -d "\t\n\r" > /app/www/build/crunchbutton.html
$PHPPATH -f ./_build.php cockpit.la $PHPPATH | tr -d "\t\n\r" > /app/www/build/cockpit.html

$PHPPATH -f ./_build.php crunchbutton.com assets/css/bundle.css s=style $PHPPATH > /app/www/assets/css/bundle.css
$PHPPATH -f ./_build.php cockpit.la assets/cockpit/css/bundle.css s=cockpit $PHPPATH > /app/www/assets/cockpit/css/bundle.css

$PHPPATH -f ./_build.php crunchbutton.com assets/js/bundle.js s=app $PHPPATH > /app/www/assets/js/bundle.js
$PHPPATH -f ./_build.php cockpit.la assets/cockpit/js/bundle.js s=cockpit $PHPPATH > /app/www/assets/cockpit/js/bundle.js

mv /app/www/.htaccess.heroku /app/www/.htaccess
