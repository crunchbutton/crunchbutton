<?php

class Controller_admin_phpinfo extends Crunchbutton_Controller_Account {
	public function init() {
		
		phpinfo();
	}
}