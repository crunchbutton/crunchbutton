#!/usr/bin/env bash

PHPPATH=$1/.heroku/php/bin/php

$PHPPATH -f ./_build.php crunchbutton.com $PHPPATH | tr -d "\t\n\r" > ../www/build/crunchbutton.html
$PHPPATH -f ./_build.php cockpit.la $PHPPATH | tr -d "\t\n\r" > ../www/build/cockpit.html

$PHPPATH -f ./_build.php crunchbutton.com /assets/css/bundle.css?s=style $PHPPATH | tr -d "\t\n\r" > ../www/assets/css/bundle.css
$PHPPATH -f ./_build.php cockpit.la /assets/cockpit/css/bundle.css?s=cockpit $PHPPATH | tr -d "\t\n\r" > ../www/assets/cockpit/css/bundle.css

$PHPPATH -f ./_build.php crunchbutton.com /assets/js/bundle.js?s=app $PHPPATH | tr -d "\t\n\r" > ../www/assets/js/bundle.js
$PHPPATH -f ./_build.php cockpit.la /assets/cockpit/js/bundle.js?s=cockpit $PHPPATH | tr -d "\t\n\r" > ../www/assets/cockpit/js/bundle.js

mv ../www/.htaccess.heroku ../www/.htaccess