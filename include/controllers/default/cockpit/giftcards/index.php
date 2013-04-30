<?php

class Controller_giftcards extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->page = 'giftcards';

		if( c::getPagePiece(2) == 'new' ){ 
			$id_user = $_GET[ 'id_user' ];
			if( $id_user != '' ){
				$user = Crunchbutton_User::o( $id_user );
				if( $user->id_user ){
					c::view()->user = $user;
				} else {
					c::view()->user = false;
				}
			} else {
				c::view()->user = false;
			}
			if( $_GET[ 'id_restaurant' ] != '' ){ 
				$id_restaurant = $_GET[ 'id_restaurant' ];
			} else {
				$id_restaurant = false;
			}
			if( $_GET[ 'id_order_reference' ] != '' ){ 
				$id_order_reference = $_GET[ 'id_order_reference' ];
			} else {
				$id_order_reference = false;
			}
			c::view()->id_order_reference = $id_order_reference;
			c::view()->id_restaurant = $id_restaurant;
			c::view()->users = Crunchbutton_User::q('SELECT DISTINCT( u.id_user ), u.name, u.phone, u.email FROM user u INNER JOIN user_auth ua ON ua.id_user = u.id_user WHERE u.active = 1 ORDER BY u.name ASC');;
			c::view()->display('giftcards/new');

		} else if( c::getPagePiece(2) == 'sms' ){ 
			c::view()->display('giftcards/sms');
	
		} else if( c::getPagePiece(2) == 'email' ){ 
			c::view()->display('giftcards/email');

		} else {

			$giftcard = Crunchbutton_Promo::o(c::getPagePiece(2));
			if ($giftcard->id_promo) {
				c::view()->users = Crunchbutton_User::q('SELECT u.id_user, u.name, u.phone, u.email FROM user u INNER JOIN user_auth ua ON ua.id_user = u.id_user WHERE u.active = 1 ORDER BY u.name ASC');;
				c::view()->giftcard = $giftcard;
				c::view()->display('giftcards/giftcard');	
			} else {
				// Show the credit's list
				c::view()->display('giftcards/index');
			}
		}
	}
}