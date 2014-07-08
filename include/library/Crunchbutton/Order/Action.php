<?php

class Crunchbutton_Order_Action extends Cana_Table {
	const DELIVERY_PICKEDUP   = 'delivery-pickedup';
	const DELIVERY_ACCEPTED   = 'delivery-accepted';
	const DELIVERY_REJECTED   = 'delivery-rejected';
	const DELIVERY_DELIVERED  = 'delivery-delivered';
	const DELIVERY_TRANSFERED  = 'delivery-transfered';
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

	public function byOrder( $id_order ){
		$_id_order = ( $this->id_order ? $this->id_order : $id_order );
		return Crunchbutton_Order_Action::q( "SELECT oa.*, a.name FROM order_action oa INNER JOIN admin a ON oa.id_admin = a.id_admin WHERE oa.id_order = {$_id_order} ORDER BY oa.id_order_action DESC" );
	}

	public function restaurant(){
		return Crunchbutton_Restaurant::q( "SELECT r.* FROM restaurant r INNER JOIN `order` o ON o.id_restaurant = r.id_restaurant  WHERE id_order = {$this->id_order}" );
	}

	public function changeTransferDeliveryDriver( $id_order, $id_admin ){
		// for payment - #3232
		$action = new Order_Action;
		$action->id_order = $id_order;
		$action->id_admin = $id_admin;
		$action->timestamp = date('Y-m-d H:i:s');
		$action->type = Crunchbutton_Order_Action::DELIVERY_TRANSFERED;
		$action->note = 'Transfer made by ' . c::admin()->name . ' #' . c::admin()->id_admin;
		$action->save();
		return $action->id_order_action;
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->timestamp, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
		}
		return $this->_date;
	}

	public function dateAtTz( $timezone ) {
		$date = new DateTime( $this->timestamp, new DateTimeZone( c::config()->timezone ) );
		$date->setTimezone( new DateTimeZone( $timezone ) );
		return $date;
	}
	public function admin(){
		return Admin::o( $this->id_admin );
	}

}