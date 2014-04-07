#!/usr/bin/php
<?php
//local

$file = '../db/dump.sql';

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);

if (trim(`whoami`) == 'arzynik') {
	ini_set('mysqli.default_socket','/Applications/MAMP/tmp/mysql/mysql.sock');
	$dump = '/Applications/MAMP/Library/bin/mysqldump';
} else {
	$dump = 'mysqldump';
}

require_once('../include/crunchbutton.php');


$connect = c::config()->db->{c::app()->env()};

$cmd[] = 'rm '.$file;
$cmd[] = $dump.' -d -u '.$connect->user.' -p'.$connect->pass.' '.$connect->db.' >> '.$file;
$cmd[] = $dump.' --no-create-info --skip-triggers -u '.$connect->user.' -p'.$connect->pass.' '.$connect->db.' config group site >> '.$file;
$cmd[] = 'sed  "s/\`devin\`@\`%\`/\`root\`@\`localhost\`/g" '.$file.' > '.$file.'tmp';
$cmd[] = 'mv '.$file.'tmp '.$file;

foreach ($cmd as $c) {
	exec($c);
}

