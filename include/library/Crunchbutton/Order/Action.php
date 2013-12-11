<?php

class Crunchbutton_Order_Action extends Cana_Table {

	const DELIVERY_PICKEDUP   = 'delivery-pickedup';
	const DELIVERY_ACCEPTED   = 'delivery-accepted';
	const DELIVERY_REJECTED   = 'delivery-rejected';
	const DELIVERY_DELIVERED  = 'delivery-delivered';
	const RESTAURANT_ACCEPTED = 'restaurant-accepted';
	const RESTAURANT_REJECTED = 'restaurant-rejected';
	const RESTAURANT_READY		= 'restaurant-ready';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_action')
			->idVar('id_order_action')
			->load($id);
	}

	public function admin(){
		return Admin::o( $this->id_admin );
	}

}