<?php
/**
 * Global controller model
 *
 * @author    Devin Smith <devins@devin-smith.com>
 * @date    2010.06.03
 *
 * The controller model is excended by all controllers. Global controller
 * methods go here.
 *
 */

class Colaby_Controller extends Cana_Model {
    public function __construct() {
    	if (!isset($this->skipAuth) && empty(Cana::auth()->login()->id_login) && Cana::config()->domain->private) {
    		Cana::view()->layout('layout/login');
    		Cana::view()->display('login/index');
    		exit;
    	}
    }
} 