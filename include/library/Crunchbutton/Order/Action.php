<?php

class Crunchbutton_Order_Action extends Cana_Table {

	const DELIVERY_NEW   = 'delivery-new';
	const DELIVERY_PICKEDUP   = 'delivery-pickedup';
	const DELIVERY_ACCEPTED   = 'delivery-accepted';
	const DELIVERY_REJECTED   = 'delivery-rejected';
	const DELIVERY_CANCELED   = 'delivery-canceled';
	const DELIVERY_DELIVERED  = 'delivery-delivered';
	const DELIVERY_TRANSFERED  = 'delivery-transfered';
	const RESTAURANT_ACCEPTED = 'restaurant-accepted';
	const RESTAURANT_REJECTED = 'restaurant-rejected';
	const RESTAURANT_READY		= 'restaurant-ready';
	const DELIVERY_ORDER_TEXT_5_MIN = 'delivery-text-5min';
	const TICKET_NOT_GEOMATCHED = 'ticket-not-geomatched';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_action')
			->idVar('id_order_action')
			->load($id);
	}

	public static function byOrder( $id_order ){
		return Crunchbutton_Order_Action::q('
			SELECT oa.*, a.name, a.phone FROM order_action oa
			INNER JOIN admin a ON oa.id_admin = a.id_admin
			WHERE oa.id_order = ?
			and oa.type!=?
			ORDER BY oa.id_order_action DESC
		', [$id_order, self::TICKET_NOT_GEOMATCHED]);
	}

	public function restaurant(){
		return Crunchbutton_Restaurant::q('SELECT r.* FROM restaurant r INNER JOIN `order` o ON o.id_restaurant = r.id_restaurant  WHERE id_order = ?', [$this->id_order]);
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

	public function ordersDeliveryByAdminPeriod( $id_admin, $date_start, $date_end ){
		// convert the shift to LA timezone
		$date_start = new DateTime( $date_start, new DateTimeZone( c::config()->timezone ) );
		$date_end = new DateTime( $date_end, new DateTimeZone( c::config()->timezone ) );

		// get orders delivered at this period
		$query = 'SELECT DISTINCT( o.id_order ) id, oa.* FROM `order` o
								INNER JOIN order_action oa ON oa.id_order = o.id_order
								WHERE
									oa.type = ?
									AND oa.id_admin = ?
									AND o.date >= ?
									AND o.date <= ?
										';
		return Crunchbutton_Order_Action::q( $query, [Crunchbutton_Order_Action::DELIVERY_DELIVERED, $id_admin, $date_start->format( 'Y-m-d' ) . ' 00:00:00', $date_end->format( 'Y-m-d' ) . ' 23:59:59']);
	}

	public function minutesToDelivery(){

		$status = $this->order()->status();

		if ($status->pickedup() && $status->delivered()){
			$pickedup_date = new DateTime($status->pickedup()['date'], new DateTimeZone( c::config()->timezone ) );
			$delivered_date = new DateTime($status->delivered()['date'], new DateTimeZone( c::config()->timezone ) );
			return Util::intervalToSeconds( $delivered_date->diff( $pickedup_date ) ) / 60;
		}
		return 0;
	}

	public function dateAtTz( $timezone ) {
		$date = new DateTime( $this->timestamp, new DateTimeZone( c::config()->timezone ) );
		$date->setTimezone( new DateTimeZone( $timezone ) );
		return $date;
	}

	public function admin(){
		return Admin::o($this->id_admin);
	}

	public function order() {
		return Order::o($this->id_order);
	}
}
