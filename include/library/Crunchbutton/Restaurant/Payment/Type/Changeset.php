<?php

class Crunchbutton_Restaurant_Payment_Type_Changeset extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_payment_type_change_set')
			->idVar('id_restaurant_payment_type_change_set')
			->load($id);
	}
}