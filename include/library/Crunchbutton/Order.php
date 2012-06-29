<?php

class Crunchbutton_Order extends Cana_Table {
	public function process($params) {
		//$this->_payment = 
	}
	
	public function verifyPayment() {
		switch ($this->pay_type) {
			case 'cash':
				return true;
				break;

			case 'credit':
				$r = Charge::charge([
					'amount' => 100,
					'number' => $this->request()['number'],
					'exp_month' => $this->request()['exp_month'],
					'exp_year' => $this->request()['exp_year'],
					'name' => $this->request()['name']
				]);

				break;
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order')
			->idVar('id_order')
			->load($id);
	}
}