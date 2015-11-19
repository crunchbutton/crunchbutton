<?php

include(dirname(__FILE__).'/gitupdate.php');


if (file_exists('/home/'.$params->path.'/vendor')) {
	$cmd = 'su -l deploy -c "composer update --ignore-platform-reqs"';
} else {
	$cmd = 'su -l deploy -c "composer install --ignore-platform-reqs"';
}

exec($cmd.' 2>&1 &', $o);
echo implode("\n", $o);
