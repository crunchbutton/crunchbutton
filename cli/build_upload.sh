#!/bin/sh
cd /app/cli && /app/cli/build.sh
cd /app/cli && /app/cli/_build_upload.php -e=live
