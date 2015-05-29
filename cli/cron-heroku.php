#!/usr/bin/env php
<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
//set_time_limit(100);



echo "Starging worker...\n";
echo dirname(__FILE__)."\n";

// uncomment this
//require_once('/app/include/crunchbutton.php');

while (true) {
	// put normal cron stuff in here
	echo "Worker running...\n";

	// example
	//$o = Order::q('select * from `order` order by id_order desc limit 1')->get(0);
	//print_r($o->properties());
	
	sleep(15);
}
