<?php

exec('/usr/bin/sudo -u deploy /home/server/deploy/gitfetch.sh 2>&1 &', $o);
print_r($o);
	
exit;