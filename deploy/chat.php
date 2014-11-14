<?php

include(dirname(__FILE__).'/gitupdate.php');

echo "\nRestarting services...\n";

$cmds = [
	'service nginx restart',
	'service chat restart'
];
foreach ($cmds as $cmd) {
	$o = null;
	exec($cmd.' 2>&1 &', $o);
	echo implode("\n", $o)."\n";
}