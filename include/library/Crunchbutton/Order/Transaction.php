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

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_transaction')
			->idVar('id_order_transaction')
			->load($id);
	}
}