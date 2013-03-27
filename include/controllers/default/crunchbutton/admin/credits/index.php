<?php

class Controller_Admin_Credits extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->page = 'admin/credits';
		c::view()->layout('layout/admin');
		if( c::getPagePiece(2) == 'new' ){ 
			c::view()->users = Crunchbutton_User::q('SELECT u.id_user, u.name, u.phone, u.email FROM user u INNER JOIN user_auth ua ON ua.id_user = u.id_user WHERE u.active = 1 ORDER BY u.name ASC');;
			c::view()->display('admin/credits/new');	
		} else {
			$credit = Crunchbutton_Credit::o(c::getPagePiece(2));
			if( $credit->id_credit ){
				c::view()->credit = $credit;
				c::view()->display('admin/credits/credit');	
			} else {
				// Show the credit's list
				c::view()->display('admin/credits/index');
			}
		}
	}
}