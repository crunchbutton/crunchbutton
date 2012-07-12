<?php

class Controller_listorders extends Cana_Controller {
	public function init() {
		c::view()->layout('layout/basic');
		c::view()->display('listorders/index');
	}
}