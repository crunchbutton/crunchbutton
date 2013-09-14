<?php

class Crunchbutton_Controller_Rest extends Cana_Controller_Rest {
	
    public function __construct() {
    	$find = '/(api\.|log\.)/';
    	if (preg_match($find, $_SERVER['SERVER_NAME'])) {
    		$allow = preg_replace($find,'',$_SERVER['SERVER_NAME']);
			header('Access-Control-Allow-Origin: http'.($_SERVER['HTTPS'] == 'on' ? 's' : '').'://'.$allow);
			header('Access-Control-Allow-Credentials: true');
    	}

    	header('Content-Type: application/json');
		Cana::view()->layout('layout/ajax');
		parent::__construct();
	}
} 