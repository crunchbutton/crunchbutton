#!/bin/sh

echo "done." > /var/log/build.log
mkdir /tmp/min
cd /app/cli
/app/cli/build.sh
/app/cli/_build_upload.php -e=live
tail -f /var/log/build.log
