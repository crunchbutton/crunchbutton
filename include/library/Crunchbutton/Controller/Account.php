<?php

class Crunchbutton_Controller_Account extends Cana_Controller {
    public function __construct() {
    	if (c::config()->site->name == 'Cockpit2') {
    		if (!c::admin()->id_admin) {
    			// just prevents any other pages from being displayed since this is a blank page basicly
    			c::view()->display('home/login');
    			exit;
    		}
	    	
    	} else {
			if (!c::admin()->id_admin) {
			    header('WWW-Authenticate: Basic realm="Crunchbutton - '.$_SERVER['PHP_AUTH_USER'].'"');
			    header('HTTP/1.0 401 Unauthorized');
			    die('unauth.');
			    exit;
			}
			c::view()->layout('layout/core');
		}
    }
}