<?php

class Controller_Admin_Support extends Crunchbutton_Controller_Account {
	public function init() {
		$support = Support::o(c::getPagePiece(2));
		c::view()->page = 'admin/support';
		c::view()->layout('layout/admin');
		if( $support->id_support ){
			// Show the support's form
			c::view()->support = $support;
			c::view()->display('admin/support/support');	
		} else {
			// Show the support's list
			c::view()->display('admin/support/index');
		}
	}
}