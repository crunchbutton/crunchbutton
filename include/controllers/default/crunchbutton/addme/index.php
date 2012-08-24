<?php

class Controller_addme extends Cana_Controller {
	public function init() {
		c::view()->layout('layout/landing');
		c::view()->display('addme/index');
	}
}