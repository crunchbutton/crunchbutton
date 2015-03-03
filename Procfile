web: vendor/bin/heroku-php-apache2 www/
worker: while true; do php cli/cron-heroku.php; sleep 60; done