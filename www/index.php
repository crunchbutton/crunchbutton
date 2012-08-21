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

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);
set_time_limit(10);
ini_set('zlib.output_compression','On');
ini_set('zlib.output_compression_level',9);

if (isset($_REQUEST['__url']) && $_REQUEST['__url'] == 'index.php') {
	$_REQUEST['__url'] = '';
}

if (preg_match('/^www\..*$/',$_SERVER['SERVER_ADDR'])) {
	header('Location: http:/'.str_replace('www',$_SERVER['SERVER_ADDR']).$_SERVER['REQUEST_URI']);
	exit;
}

require_once '../include/crunchbutton.php';
Cana::app()->displayPage();