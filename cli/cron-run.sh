#!/bin/sh

echo "* * * * * root /app/cli/master_cron.sh > /app/cron.log 2>&1" >> /etc/crontab
rsyslogd
cron
touch /app/cron.log
tail -f /app/cron.log
