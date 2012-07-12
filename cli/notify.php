#!/usr/bin/php
<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors',true);
set_time_limit(10);

require_once('../include/crunchbutton.php');

$notification = new Notification($argv[1]);
$notification->send();

//exec('nohup /usr/bin/my-command > /dev/null 2>&1 &');