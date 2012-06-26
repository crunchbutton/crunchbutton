<?php

class Controller_help extends Cana_Controller {
	public function init() {
		Cana::view()->display('help/index');
	}
}