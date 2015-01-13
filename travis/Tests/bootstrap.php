<?php

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);
putenv('PHPUNIT=1');

if (trim(`whoami`) == 'mmeyers') {
	$GLOBALS['host-crunchbutton'] = 'http://localhost:8888/';
} elseif (trim(`whoami`) == 'arzynik') {
	$GLOBALS['host-crunchbutton'] = 'http://crunchbutton.localhost/';
} else {
	$GLOBALS['host-crunchbutton'] = 'http://localhost/';
}
//die($_GLOBALS['host-crunchbutton']);

if (trim(`whoami`) == 'arzynik' || trim(`whoami`) == 'mmeyers') {
	ini_set('mysqli.default_socket','/Applications/MAMP/tmp/mysql/mysql.sock');
}

require_once('include/crunchbutton.php');

