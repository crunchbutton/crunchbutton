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

	$params = json_decode($q->server()->params);

//	$cmd = dirname(__FILE__).'/'.$q->server()->script.' -v '.$q->version.' '.$q->server()->params;
//	exec($cmd.' 2>&1 &', $o);
	ob_start();
	include(dirname(__FILE__).'/'.$q->server()->script);
	$log = ob_get_contents();
	ob_end_clean();

//	$error = implode("\n", $o);

	if (preg_match('/fatal:|\#\!\/bin\/sh|No such file or directory|Already up-to-date/', $log)) {
		$q->status = 'failed';
	} else {
		$q->status = 'success';
	}

	$q->log = $log;
	$q->save();
	exit;
}
exit;