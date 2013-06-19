<?php

class Controller_orders_newusers extends Crunchbutton_Controller_Account {
	public function init() {

		if( c::getPagePiece( 2 ) == 'sendemail' ){ 
			Crunchbutton_Newusers::queSendEmail();
		} else {
			c::view()->config = Crunchbutton_Newusers::getConfig();
			c::view()->orders = Crunchbutton_Newusers::getNewOnes();
			c::view()->display('orders/newusers');
		}
	}
}