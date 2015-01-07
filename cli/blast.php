<?php
//	/usr/bin/env php
///usr/local/bin/php

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);

require_once('../include/crunchbutton.php');

$que = Crunchbutton_Blast::getQue();

foreach ($que as $q) {
	$q->run();
}
exit;