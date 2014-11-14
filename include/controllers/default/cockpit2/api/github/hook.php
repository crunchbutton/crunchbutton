<?php

exec('/usr/bin/sudo -u deploy /home/server/deploy/gitfetch.sh', $o);
print_r($o);
	
exit;