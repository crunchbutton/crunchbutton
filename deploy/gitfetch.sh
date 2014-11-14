#!/bin/sh

# for this to work apache ALL=(deploy) NOPASSWD: /home/server/deploy/gitfetch.sh must be added to sudoers

cd /home/server && git fetch