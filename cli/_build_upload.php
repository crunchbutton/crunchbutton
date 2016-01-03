#!/usr/bin/env php
<?php

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', true);
set_time_limit(100);

require_once('../include/crunchbutton.php');

$start = time();
$end = $start + 59;

echo "\x1B[44mRunning Queue...\x1B[0m\n";
while (time() < $end) {
	$c = Crunchbutton_Queue::process();
	echo $c ? ("\x1B[32mFinished running ".$c." queue items\x1B[0m\n") : "\x1B[31mQueue is empty.\x1B[0m\n";
	sleep(1);
}
echo "Done.\n\n";
