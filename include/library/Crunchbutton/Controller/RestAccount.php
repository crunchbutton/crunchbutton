<?php

class Crunchbutton_Controller_RestAccount extends Crunchbutton_Controller_Rest {
    public function __construct() {
		if (!c::admin()->id_admin) {
		    header('WWW-Authenticate: Basic realm="Crunchbutton - '.$_SERVER['PHP_AUTH_USER'].'"');
		    header('HTTP/1.0 401 Unauthorized');
		    die('unauth.');
		    exit;
		}
		parent::__construct();
    }
}