#!/usr/bin/env bash

./_build.php crunchbutton.com | tr -d "\t\n\r" > ../www/build/crunchbutton.html
./_build.php cockpit.la | tr -d "\t\n\r" > ../www/build/cockpit.html

echo $1
ls $1
php -r "echo 'test';"
/usr/bin/env php -r "echo 'test2';"

cat ../www/.htaccess ../www/.htaccess.heroku > ../www/.htaccess.move
mv ../www/.htaccess.move ../www/.htaccess
rm -f ../www/.htaccess.heroku