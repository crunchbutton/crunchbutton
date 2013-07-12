<?php

class Crunchbutton_Controller_Account extends Cana_Controller {
    public function __construct() {
		if ($_SERVER['HTTP_AUTHORIZATION']) {
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		}
		
		$admin = Admin::login($_SERVER['PHP_AUTH_USER']);

		if (!$_SERVER['PHP_AUTH_USER'] || !$admin->id_admin || sha1(c::crypt()->encrypt($_SERVER['PHP_AUTH_PW'])) != $admin->pass) {
		    header('WWW-Authenticate: Basic realm="Crunchbutton - '.$_SERVER['PHP_AUTH_USER'].'"');
		    header('HTTP/1.0 401 Unauthorized');
		    die('unauth.');
		    exit;
		}
		
		c::admin($admin);
		$_SESSION['admin'] = true;
		c::view()->layout('layout/core');
    }
}