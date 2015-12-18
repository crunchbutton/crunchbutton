#!/usr/bin/env bash

PHPPATH=php

$PHPPATH -f cli/_build.php crunchbutton.com $PHPPATH | tr -d "\t\n\r" > www/build/crunchbutton.html
$PHPPATH -f cli/_build.php cockpit.la $PHPPATH | tr -d "\t\n\r" > www/build/cockpit.html

$PHPPATH -f cli/_build.php crunchbutton.com assets/css/bundle.css s=style $PHPPATH > www/assets/css/bundle.css
$PHPPATH -f cli/_build.php cockpit.la assets/cockpit/css/bundle.css s=cockpit $PHPPATH > www/assets/cockpit/css/bundle.css

$PHPPATH -f cli/_build.php crunchbutton.com assets/js/bundle.js s=app $PHPPATH > www/assets/js/bundle.js
$PHPPATH -f cli/_build.php cockpit.la assets/cockpit/js/bundle.js s=cockpit $PHPPATH > www/assets/cockpit/js/bundle.js

mv www/.htaccess.heroku www/.htaccess
