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

	public function byOrder( $id_order ){
		return Crunchbutton_Order_Action::q( "SELECT * FROM order_action WHERE id_order = {$this->id_order} ORDER BY id_order_action DESC" );
	}

	public function restaurant(){
		return Crunchbutton_Restaurant::q( "SELECT r.* FROM restaurant r INNER JOIN `order` o ON o.id_restaurant = r.id_restaurant  WHERE id_order = {$this->id_order}" );
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->timestamp, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
		}
		return $this->_date;
	}

	public function admin(){
		return Admin::o( $this->id_admin );
	}

}