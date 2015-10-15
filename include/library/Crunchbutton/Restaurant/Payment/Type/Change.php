<?php

class Crunchbutton_Restaurant_Payment_Type_Change extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('id_restaurant_payment_type_change')
			->idVar('id_id_restaurant_payment_type_change')
			->load($id);
	}
}