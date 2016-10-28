#!/usr/bin/env php
<?php

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', true);
set_time_limit(100);

require_once('../include/crunchbutton.php');

$start = time();
$end = $start + ($argv[1] ? $argv[1] : 59);
$wait = $argv[2] ? $argv[2] : 1;
$infinite = $argv[1] == '0' ? true : false;

if ($infinite) {
	set_time_limit(0);
}

echo "\x1B[44mRunning Queue on db:".c::env()."...\x1B[0m\n";
while (time() < $end || $infinite) {
	$c = Crunchbutton_Queue::process();
	echo $c ? ("\x1B[32mFinished running ".$c." queue items\x1B[0m\n") : "\x1B[31mQueue is empty.\x1B[0m\n";
	sleep($wait);
}
echo "Done.\n\n";
