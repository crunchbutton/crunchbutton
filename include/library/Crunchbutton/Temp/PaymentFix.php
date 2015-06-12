<?php

class Crunchbutton_Temp_PaymentFix extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('temp_paymentfix')
			->idVar('id_temp_paymentfix')
			->load($id);
	}

}
