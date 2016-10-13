#!/bin/sh

mkdir /tmp/min
cd /app/cli
/app/cli/build.sh
/app/cli/_build_upload.php -e=live
