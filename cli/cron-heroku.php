#!/usr/bin/env php
<?php
error_reporting(E_ALL);
ini_set('display_errors',true);
set_time_limit(100);



echo 'Testing...';

require_once('/app/include/crunchbutton.php');

$res = c::mailgun()->sendMessage(c::config()->mailgun->domain, [
	'from' 		=> 'test@_DOMAIN_',
	'to'		=> 'arzynik@gmail.com',
	'subject'	=> 'this is a test',
	'html'		=> 'this is some <b>content</b>'
]);



throw new Exception('exception test');

