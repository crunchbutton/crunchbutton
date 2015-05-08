#!/usr/bin/php
<?php
//local

$file = '../db/dump.sql';

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);

if (trim(`whoami`) == 'arzynik') {
	ini_set('mysqli.default_socket','/Applications/MAMP/tmp/mysql/mysql.sock');
	$dump = '/Applications/MAMP/Library/bin/mysqldump';
	$mysql = '/Applications/MAMP/Library/bin/mysql';
} else {
	$dump = 'mysqldump';
	$mysql = 'mysql';
}

require_once('../include/crunchbutton.php');


$remote = c::config()->db->live;
$local = c::config()->db->local;

$cmd[] = 'rm '.$file;
$cmd[] = $dump.' --lock-tables=false -h 45.56.80.7 -u '.c::crypt()->decrypt($remote->user).' -p'.c::crypt()->decrypt($remote->pass).' '.$remote->db.' >> '.$file;
//$cmd[] = $dump.' --no-create-info --skip-triggers -u '.$connect->user.' -p'.$connect->pass.' '.$connect->db.' config group site >> '.$file;
$cmd[] = 'sed  "s/\`devin\`@\`%\`/\`root\`@\`localhost\`/g" '.$file.' > '.$file.'tmp';
$cmd[] = 'mv '.$file.'tmp '.$file;

// import into local db
$cmd[] = $mysql.' -u '.$local->user.' -p'.$local->pass.' '.$local->db.' < '.$file;

print_r($cmd);
exit;

foreach ($cmd as $c) {
	exec($c);
}

