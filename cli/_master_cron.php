#!/usr/bin/env php
<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
set_time_limit(0);

require_once('../include/crunchbutton.php');
echo "\x1B[44mRunning Cron on db:".c::env()."...\x1B[0m\n";

while (true) {
	echo "\x1B[32mStarting cron...\x1B[0m\n";
	Crunchbutton_Cron_Log::start();
	echo "\x1B[32mCron complete.\x1B[0m\n";
	sleep(60);
}

echo "Done.\n\n";