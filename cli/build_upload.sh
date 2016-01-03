#!/bin/sh
cd /app/cli && /app/cli/build.sh
echo "starting..." > /var/log/build.log
tail -f /var/log/build.log && cd /app/cli && /app/cli/_build_upload.php -e=live
