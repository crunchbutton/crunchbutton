#!/bin/sh
cd /app/cli && /app/cli/build.sh
tail -f /run.sh
#cd /app/cli && /app/cli/_build_upload.php -e=live
