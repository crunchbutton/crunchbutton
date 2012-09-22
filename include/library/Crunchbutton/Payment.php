<?php

class Crunchbutton_Payment extends Cana_Table {
	public static function credit($params = null) {
		$payment = new Payment((object)$params);
		$payment->date = date('Y-m-d H:i:s');
		$credit = Crunchbutton_Balanced_Credit::credit($payment->restaurant(), $payment->amount, $payment->note);
		$payment->balanced_id = $credit->id;
		$payment->save();
	}
	
	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('payment')
			->idVar('id_payment')
			->load($id);
	}
}