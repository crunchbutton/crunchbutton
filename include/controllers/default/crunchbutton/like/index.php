<?php

class Controller_like extends Cana_Controller {
	public function init() {
		c::view()->layout('layout/micro');
		c::view()->display('like/index');
	}
}