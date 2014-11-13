#!/usr/bin/env php
<?php
//#!/usr/local/bin/php

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);

require_once('../include/crunchbutton.php');

$host = gethostname();
$que = Cockpit_Deploy_Version::getQue($host);

foreach ($que as $q) {
	$q->status = 'deploying';
	$q->save();

	$cmd = dirname(__FILE__).'/'.$q->server()->script.' '.$q->server()->params;
	exec($cmd.' 2>&1 &', $o);

	$error = implode("\n", $o);

	$q->status = 'success';
	$q->log = $error;
	$q->save();
}
exit;