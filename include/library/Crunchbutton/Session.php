<?php

class Crunchbutton_Session extends Crunchbutton_Session_Adapter implements SessionHandlerInterface {
	public function __construct($id = null) {
		parent::__construct();
	}
}