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

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors',true);
set_time_limit(10);

if (isset($_REQUEST['__url']) && $_REQUEST['__url'] == 'index.php') {
	$_REQUEST['__url'] = '';
}

require_once '../include/crunchbutton.php';
Cana::app()->displayPage();