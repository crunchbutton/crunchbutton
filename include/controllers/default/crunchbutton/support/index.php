<?php

class Controller_support extends Cana_Controller {
	public function init() {
		Cana::view()->display('support/index');
	}
}