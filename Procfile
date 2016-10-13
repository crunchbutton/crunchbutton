web: vendor/bin/heroku-php-nginx -C conf/nginx.conf www/
cron: cd /app/cli && php /app/cli/_master_cron.php loop -e=crondb
queue: cd /app/cli && php /app/cli/_queue.php 0 15 -e=crondb