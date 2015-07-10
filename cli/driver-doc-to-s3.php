#!/usr/bin/env php
<?php

date_default_timezone_set('America/Los_Angeles');

require_once('../include/library/Crunchbutton/S3.php');

$key = 'AKIAJASPPY2YFBVPMY4A';
$secret = '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';
$bucket = 'crunchbutton-image-restaurant';
$path = '/home/cockpit.la/www/upload/drivers-doc/';


S3::setAuth($key, $secret);

$dir = new DirectoryIterator($path);
foreach ($dir as $fileinfo) {
	if (!$fileinfo->isDot()) {
		$file = $fileinfo->getFilename();
		echo "Uploading $file...";
		$r = S3::putObject(S3::inputFile($path.$file, false), $bucket, $file, S3::ACL_PRIVATE);
		echo ($r ? 'success' : 'FAILED')."\n";
		exit;
	}
}