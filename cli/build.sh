#!/bin/sh
./_build.php crunchbutton.com | tr -d "\t\n\r" > ../www/build/crunchbutton.html
./_build.php cockpit.la | tr -d "\t\n\r" > ../www/build/cockpit.html
cat ../www/.htaccess ../www/.htaccess.heroku > ../www/.htaccess