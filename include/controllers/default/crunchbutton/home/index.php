<?php

class Controller_home extends Cana_Controller {
	public function init() {
		Cana::view()->display('home/index');
	}
}