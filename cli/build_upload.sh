#!/bin/sh
cd /app/cli && /app/cli/build.sh
tail -f /var/log/build.log && cd /app/cli && /app/cli/_build_upload.php -e=live
