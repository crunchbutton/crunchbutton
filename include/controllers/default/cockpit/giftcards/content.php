<?php

class Controller_giftcards_content extends Crunchbutton_Controller_Account {

	public function init() {

		$restaurant_id = $_REQUEST['restaurant'];
		if( !c::admin()->permission()->check( [ 'global', 'orders-all', "gift-card-list-restaurant-{$restaurant_id}", "gift-card-list-restaurant-{$restaurant_id}", "gift-card-restaurant-{$restaurant_id}" ] ) ){
			return;
		}


		$search = [];
	
		if ($_REQUEST['limit']) {
			$search['limit'] = intval($_REQUEST['limit']);
		}
		
		if ($_REQUEST['id_user']) {
			$search['id_user'] = $_REQUEST['id_user'];
		}

		if ($_REQUEST['type']) {
			$search['type'] = $_REQUEST['type'];
		}
		
		if ($_REQUEST['dates']) {
			$dates = explode(',',$_REQUEST['dates']);
			$search['start'] = $dates[0];
			$search['end'] = $dates[1];
		}
		
		if ($_REQUEST['restaurant']) {
			$search['restaurant'] = $_REQUEST['restaurant'];
		}

		c::view()->giftcards = Crunchbutton_Promo::find($search);
		c::view()->layout('layout/ajax');
		c::view()->display('giftcards/content');
	}
}