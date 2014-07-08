<?php

class Cockpit_Payment_Order_Transaction extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this->table('payment_order_transaction')->idVar('id_payment_order_transaction')->load($id);
	}

}