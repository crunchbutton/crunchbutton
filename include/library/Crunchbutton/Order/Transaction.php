<?php

class Crunchbutton_Order_Transaction extends Cana_Table {

	const TYPE_DEBIT = 'debit';
	const TYPE_CREDIT = 'credit';

	const TYPE_PAID_TO_RESTAURANT = 'paid-to-restaurant';
	const TYPE_PAID_TO_DRIVER = 'paid-to-driver';
	const PAYMENT_TYPE_GIFT = 'gift';
	const PAYMENT_TYPE_CARD = 'card';

	const SOURCE_CRUNCHBUTTON = 'crunchbutton';
	const SOURCE_RESTAURANT = 'restaurant';

	public function checkOrderWasPaidRestaurant( $id_order ){
		$query = 'SELECT * FROM order_transaction ot WHERE type = "' . Crunchbutton_Order_Transaction::TYPE_PAID_TO_RESTAURANT . '" LIMIT 1';
		$order = Cockpit_Payment_Schedule_Order::q( $query );
		if( $order->id_payment_schedule_order ){
			return true;
		}
		return false;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_transaction')
			->idVar('id_order_transaction')
			->load($id);
	}
}