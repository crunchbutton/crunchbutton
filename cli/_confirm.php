#!/usr/local/bin/php
<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
set_time_limit(10);

require_once('../include/crunchbutton.php');

$order = new Order($argv[1]);
$order->confirm();
