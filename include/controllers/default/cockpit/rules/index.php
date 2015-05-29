<?php

class Controller_Rules extends Crunchbutton_Controller_Account {
	public function init() {

		header( 'HTTP/1.1 301 Moved Permanently' );
		header( 'Location: http://cockpit.la/rules' );

		if (!c::admin()->permission()->check(['global','rules'])) {
			return ;
		}
		c::view()->rules = new Crunchbutton_Order_Rules();
		c::view()->display('rules/index');
	}
}