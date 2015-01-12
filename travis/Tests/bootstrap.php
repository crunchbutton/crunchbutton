<?php

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);
putenv('PHPUNIT=1');

if (trim(`whoami`) == 'arzynik' || trim(`whoami`) == 'mmeyers') {
	ini_set('mysqli.default_socket','/Applications/MAMP/tmp/mysql/mysql.sock');
}

require_once('include/crunchbutton.php');

