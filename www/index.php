<?php
/**
 * HTTP entry point
 *
 * @author	Devin Smith (www.devin-smith.com)
 * @date	2009.09.18
 *
 * This uses a caffeine engine base so we can reuse it in the future
 * reguardless of what it is.
 *
 */

error_reporting(E_ALL ^ ( E_NOTICE | E_STRICT | E_DEPRECATED ) );
ini_set('display_errors',true);
set_time_limit(100);
ini_set('zlib.output_compression','On');
ini_set('zlib.output_compression_level',9);



// routing config for built in web server. we only use this for travis temporarily
if (function_exists('php_sapi_name') && php_sapi_name() == 'cli-server') {
	$path = pathinfo($_SERVER['SCRIPT_FILENAME']);

	$file = substr($_SERVER['SCRIPT_NAME'],1);
	$allowed = ['scss','php'];
	if (file_exists($file) && !in_array($path['extension'], $allowed)) {

		$info = @trim(explode(':',exec('file --mime-type '.$file))[1]);

		header('Content-Type: '.$info);
		readfile($_SERVER['SCRIPT_FILENAME']);
		exit;

	} else {
		$_SERVER['SCRIPT_NAME'] = '/index.php';
	}
}


if (isset($_REQUEST['__url']) && $_REQUEST['__url'] == 'index.php') {
	$_REQUEST['__url'] = '';
}
// no reason to pass __url
if (!$_REQUEST['__url']) {
	$request = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
	$dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
	$base = substr($dir, -1) == '/' ? $dir : $dir.'/';

	$url = preg_replace('/^'.str_replace('/','\\/',''.$dir).'/','',$request);
	$url = substr($url, 0, 1) == '/' ? $url : '/'.$url;
	$_REQUEST['__url'] = substr($url, 1);
}

if (isset($_GET['__host'])) {
	setcookie('__host', $_GET['__host'], time()+60*60*24, '/');
	$_COOKIE['__host'] = $_GET['__host'];
}

if ($_COOKIE['__host']) {
	$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = $_REQUEST['__host'];
}

if (preg_match('/^www\..*$/',$_SERVER['HTTP_HOST'])) {
	header('Location: http://'.str_replace('www.','',$_SERVER['HTTP_HOST']).$_SERVER['REQUEST_URI']);
	exit;
}

if ($_ENV['DEBUG']) {
	error_log('>> PAGE START >> '.$_SERVER['REQUEST_URI']);
}

require_once '../include/crunchbutton.php';

if ($_ENV['DEBUG']) {
	error_log('>> DISPLAYING PAGE...');
}


Cana::app()->displayPage();

if ($_ENV['DEBUG']) {
	register_shutdown_function(function() {
		error_log('<< PAGE FINISHED << '.$_SERVER['REQUEST_URI']);
	});
}
exit;