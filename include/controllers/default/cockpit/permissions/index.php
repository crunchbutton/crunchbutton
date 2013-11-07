<?php

class Controller_Permissions extends Crunchbutton_Controller_Account {

	public function init() {

		c::view()->page = 'permissions';

		c::view()->display('permissions/index');

	}

}