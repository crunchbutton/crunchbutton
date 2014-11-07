#!/bin/sh

su -l deploy -c "cd /home/chat.cockpit.la && git pull"
service nginx restart
