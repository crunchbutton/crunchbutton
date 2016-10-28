web: vendor/bin/heroku-php-nginx -C conf/nginx.conf www/
cron: cd cli && php _master_cron.php
queue: cd cli && php _queue.php 0 15
build: cd cli && mkdir /tmp/min && sh build.sh && php _build_upload.php -e=live
local: cd www && php -d short_open_tag=On -S localhost:8000 index.php