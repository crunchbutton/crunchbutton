<?php

class Controller_landing extends Cana_Controller {
	public function init() {
		c::view()->layout('layout/landing');
		c::view()->display('landing/index');
	}
}