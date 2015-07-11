<?php

class Crunchbutton_User_Payment_Type extends Cana_Table {

	public static function processor() {
		return c::config()->site->config('processor_payments')->value;
	}	

	public function getUserPaymentType($id_user = null) {
		$id_user = $id_user ? $id_user : c::user()->id_user;

		if ($id_user) {
			$payment = Crunchbutton_User_Payment_Type::q('
				SELECT * FROM user_payment_type
				WHERE
					id_user = ?
					AND active = true
					AND ' . Crunchbutton_User_Payment_Type::processor() . '_id IS NOT NULL
				ORDER BY id_user_payment_type DESC LIMIT 1
			', [$id_user]);

			if ($payment->id_user_payment_type) {
				return $payment->get(0);
			}
		}

		return false;
	}

	public function desactiveOlderPaymentsType() {
		if (!$this->id_user || !$this->id_user_payment_type) {
			return false;
		}
		self::q('select * from user_payment_type where id_user=? and id_user_payment_type!= ?', [$this->id_user, $this->id_user_payment_type])->each(function() {
			$this->deactivate();
		});
	}
	
	public function deactivate() {
		$this->active = 0;
		$this->save();
	}
	
	public function user() {
		return User::o($this->id_user);
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('user_payment_type')
			->idVar('id_user_payment_type')
			->load($id); 
	}
}