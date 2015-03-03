<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
set_time_limit(100);

require_once('../include/crunchbutton.php');

echo 'Testing...';

$res = c::mailgun()->sendMessage(c::config()->mailgun->domain, [
	'from' 		=> 'test@_DOMAIN_',
	'to'		=> 'arzynik@gmail.com',
	'subject'	=> 'this is a test',
	'html'		=> 'this is some <b>content</b>'
]);



error_log('error_log');

$stderr = fopen('php://stderr', 'w'); 
fwrite($stderr,'stderror'); 
fclose($stderr); 

