<?php

class Crunchbutton_Controller_Rest extends Cana_Controller_Rest {
	
    public function __construct() {
    	header('Content-Type: application/json');
		Cana::view()->layout('layout/ajax');
		parent::__construct();
	}
} 