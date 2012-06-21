<?php

class Controller_error extends Cana_Controller {
	public function init() {
		Cana::view()->display('home/index');
	}
}