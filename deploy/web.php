<?php

include(dirname(__FILE__).'/gitupdate.php');


if (file_exists('/home/'.$params->path.'/vendor')) {
	$cmd = 'su -l deploy -c "cd /home/'.$params->path.' && composer update --ignore-platform-reqs"';
} else {
	$cmd = 'su -l deploy -c "cd /home/'.$params->path.' && composer install --no-dev --ignore-platform-reqs"';
}

exec($cmd.' 2>&1 &', $o);
echo implode("\n", $o);
