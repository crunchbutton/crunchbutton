<?php

class Crunchbutton_Controller_Account extends Cana_Controller {
    public function __construct() {
    	if (1==2 && !Cana::user()->id_user) {
    		Cana::view()->display('login/index');
    		exit;
    	}
    }
}