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
ini_set('max_input_vars', 100000);
ini_set('upload_max_filesize', '32M');
ini_set('post_max_size', '32M');


if (isset($_REQUEST['__url']) && $_REQUEST['__url'] == 'index.php') {
	$_REQUEST['__url'] = '';
}

if (isset($_GET['__host'])) {
	setcookie('__host', $_GET['__host'], 0, '/');
	$_COOKIE['__host'] = $_GET['__host'];
}

if ($_COOKIE['__host']) {
	$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = $_REQUEST['__host'];
}

if (preg_match('/^www\..*$/',$_SERVER['HTTP_HOST'])) {
	header('Location: http://'.str_replace('www.','',$_SERVER['HTTP_HOST']).$_SERVER['REQUEST_URI']);
	exit;
}

require_once '../include/crunchbutton.php';
Cana::app()->displayPage();