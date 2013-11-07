<?php

class Controller_orders_lastusers extends Crunchbutton_Controller_Account {
	public function init() {
		if (!c::admin()->permission()->check(['global','orders-all','orders-new-users'])) {
			return ;
		}
		if( c::getPagePiece(2) == 'content' ){
			$limit = c::getPagePiece(3);
			c::view()->orders = Crunchbutton_Newusers::getLastOnes( $limit );	
			c::view()->layout('layout/ajax');
			c::view()->display('orders/lastusers/content');
			exit;
		}
		c::view()->display('orders/lastusers/index');
	}
}