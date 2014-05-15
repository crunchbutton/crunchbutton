<?php

class Crunchbutton_Controller_RestAccount extends Crunchbutton_Controller_Rest {
    public function __construct() {
    	if (c::config()->site->name == 'Cockpit2') {
    		if (!c::admin()->id_admin) {
    			// just prevents any other pages from being displayed since this is a blank page basicly
    			header('HTTP/1.1 401 Unauthorized');
    			exit;
    		}
	    	
    	} else {
			if (!c::admin()->id_admin) {
			    header('WWW-Authenticate: Basic realm="Crunchbutton - '.$_SERVER['PHP_AUTH_USER'].'"');
			    header('HTTP/1.0 401 Unauthorized');
			    die('unauth.');
			}
		}
		
		parent::__construct();
    }
}