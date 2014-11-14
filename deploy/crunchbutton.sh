#!/bin/sh

su -l deploy -c "cd /home/$1 && git pull"