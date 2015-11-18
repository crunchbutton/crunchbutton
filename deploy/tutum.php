<?php

echo "\nUpdating git at /home/".$params->path."...\n";

if ($q->tag) {
	$gitcmd = 'git checkout tags/'.$q->tag;
} else {
	$gitcmd = 'git checkout '.$q->version;
}

$cmd = 'su -l deploy -c "cd /home/'.$params->path.' && git fetch && '.$gitcmd.'"';
exec($cmd.' 2>&1 &', $o);
echo implode("\n", $o);
