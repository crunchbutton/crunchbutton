webnginx: vendor/bin/heroku-php-nginx -C conf/nginx.conf www/
web: vendor/bin/heroku-php-nginx -C conf/nginx.conf -F conf/fpm.conf www/
apache: vendor/bin/heroku-php-apache2 www/
worker: cli/cron-heroku.php