web: vendor/bin/heroku-php-nginx -C conf/nginx.conf www/
cron: cd /app/cli && php /app/cli/_master_cron.php
queue: cd /app/cli && php /app/cli/_queue.php 0 15
build: cd /app/cli && mkdir /tmp/min && sh build.sh && php _build_upload.php -e=live