<?php

class Controller_Admin_Giftcards extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->page = 'admin/giftcards';
		c::view()->layout('layout/admin');
		if( c::getPagePiece(2) == 'new' ){ 
			c::view()->users = Crunchbutton_User::q('SELECT u.id_user, u.name, u.phone, u.email FROM user u INNER JOIN user_auth ua ON ua.id_user = u.id_user WHERE u.active = 1 ORDER BY u.name ASC');;
			c::view()->display('admin/giftcards/new');
		} else if( c::getPagePiece(2) == 'sms' ){ 
			c::view()->display('admin/giftcards/sms');	
		} else if( c::getPagePiece(2) == 'email' ){ 
			c::view()->display('admin/giftcards/email');	
		} else {
			$giftcard = Crunchbutton_Promo::o(c::getPagePiece(2));
			if( $giftcard->id_promo ){
				c::view()->users = Crunchbutton_User::q('SELECT u.id_user, u.name, u.phone, u.email FROM user u INNER JOIN user_auth ua ON ua.id_user = u.id_user WHERE u.active = 1 ORDER BY u.name ASC');;
				c::view()->giftcard = $giftcard;
				c::view()->display('admin/giftcards/giftcard');	
			} else {
				// Show the credit's list
				c::view()->display('admin/giftcards/index');
			}
		}
	}
}