#!/usr/bin/php
<?php
//local

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);

if (trim(`whoami`) == 'arzynik') {
	ini_set('mysqli.default_socket','/Applications/MAMP/tmp/mysql/mysql.sock');
	$dump = '/Applications/MAMP/Library/bin/mysqldump';
} else {
	$dump = 'mysqldump';
}

// include our libraries AFTER the nap, so we dont keep mysql or our memory awake
require_once('../include/crunchbutton.php');


$connect = c::config()->db->{c::app()->env()};

$cmd[] = 'rm dbdump.sql';
$cmd[] = $dump.' -d -u '.$connect->user.' -p'.$connect->pass.' '.$connect->db.' >> dbdump.sql';
$cmd[] = $dump.' --no-create-info --skip-triggers -u '.$connect->user.' -p'.$connect->pass.' '.$connect->db.' config group site >> dbdump.sql';

foreach ($cmd as $c) {
	exec($c);
}

