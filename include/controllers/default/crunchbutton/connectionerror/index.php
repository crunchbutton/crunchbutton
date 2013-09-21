<?php

class Controller_ConnectionError extends Cana_Controller {
	public function init() {
		Cana::view()->display('connectionerror/index');
	}
}