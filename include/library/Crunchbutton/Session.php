<?php

class Crunchbutton_Session extends Crunchbutton_Session_Adapter implements SessionHandlerInterface {
	public function __construct($id = null) {
		session_set_save_handler(
			[$this, 'open'],
			[$this, 'close'],
			[$this, 'read'],
			[$this, 'write'],
			[$this, 'destroy'],
			[$this, 'gc']
		);
		parent::__construct();
	}
}