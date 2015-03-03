#!/usr/bin/env php
<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
//set_time_limit(100);



echo "Starging worker...\n";

require_once('/app/include/crunchbutton.php');

while (true) {
	echo "Worker running...\n";
	sleep(15);
}
