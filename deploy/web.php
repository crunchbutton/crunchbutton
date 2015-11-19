<?php

include(dirname(__FILE__).'/gitupdate.php');

$cmd = 'su -l deploy -c "cd /home/'.$params->path.' && composer install --no-dev --ignore-platform-reqs"';

exec($cmd.' 2>&1 &', $o);
echo implode("\n", $o);
