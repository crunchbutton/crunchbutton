<?php

class Controller_legal extends Cana_Controller {
	public function init() {
		Cana::view()->display('legal/index');
	}
}