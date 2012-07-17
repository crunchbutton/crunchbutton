<?php

class Controller_hello extends Cana_Controller {
	public function init() {
		c::view()->layout('layout/landing');
		c::view()->display('landing/index');
	}
}