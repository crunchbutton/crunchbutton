#!/bin/sh
cd /home/cockpit.crunchbutton/cli && /home/cockpit.crunchbutton/cli/dbbackup.php -e=live
mv /home/cockpit.crunchbutton/db/backup.sql /home/backup/latest.sql
