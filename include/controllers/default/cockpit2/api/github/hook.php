<?php

exec('/home/server/deploy/gitfetch.sh 2>&1 &', $o);
print_r($o);
	
exit;