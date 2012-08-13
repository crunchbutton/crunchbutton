<?php

class Crunchbutton_Controller_Account extends Cana_Controller {
    public function __construct() {
		if ($_SERVER['HTTP_AUTHORIZATION']) {
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		}

		if (!$_SERVER['PHP_AUTH_USER'] || $_SERVER['PHP_AUTH_USER'] != 'judd' || $_SERVER['PHP_AUTH_PW'] != '***REMOVED***') {
		    header('WWW-Authenticate: Basic realm="Crunchbutton - '.$_SERVER['PHP_AUTH_USER'].'"');
		    header('HTTP/1.0 401 Unauthorized');

		    die('unauth.');
		    exit;
		}
		
		$_SESSION['admin'] = true;


    }
}