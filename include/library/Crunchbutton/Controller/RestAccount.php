<?php

class Crunchbutton_Controller_RestAccount extends Crunchbutton_Controller_Rest {
	public function __construct() {
		if (c::config()->site->name == 'Cockpit2') {
			if (!c::admin()->id_admin) {
				// just prevents any other pages from being displayed since this is a blank page basicly
				$this->error(401, false);
				echo json_encode( [ 'login' => true ] );exit;
			}

		} else {
			if (!c::admin()->id_admin) {
				header('WWW-Authenticate: Basic realm="Crunchbutton - '.$_SERVER['PHP_AUTH_USER'].'"');
				$this->error(401, true);
			}
		}

		parent::__construct();
	}
}