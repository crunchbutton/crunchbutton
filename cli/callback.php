#!/usr/local/bin/php
<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
set_time_limit(10);

//sleep(5);


require_once('../include/crunchbutton.php');

$not = new Notification($argv[1]);
$order = new Order($argv[2]);
$not->send($order);
