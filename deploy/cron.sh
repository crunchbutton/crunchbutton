#!/bin/sh
# add an entry like this
# * * * * * root /home/server/deploy/cron.sh server /usr/bin/php > /home/cron-deploy.log 2>&1

cd /home/$1/cli && $2 /home/$1/deploy/cron.php -e=live