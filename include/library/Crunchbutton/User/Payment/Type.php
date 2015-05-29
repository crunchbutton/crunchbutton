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

	// @todo: shouldnt be used anymore
	public function copyPaymentFromUserTable( $id_user = null ){
		$id_user = ( $id_user ) ? $id_user : c::user()->id_user;
		if( $id_user ){
			$user = Crunchbutton_User::o( $id_user );
			if( $user->card && $user->card_exp_year && $user->card_exp_month ){
				$user_payment_type = new Crunchbutton_User_Payment_Type();
				$user_payment_type->id_user = $user->id_user;
				$user_payment_type->active = 1;
				$user_payment_type->stripe_id = $user->stripe_id;
				$user_payment_type->balanced_id = $user->balanced_id;
				$user_payment_type->card = $user->card;
				$user_payment_type->card_type = $user->card_type;
				$user_payment_type->card_exp_year = $user->card_exp_year;
				$user_payment_type->card_exp_month = $user->card_exp_month;
				$user_payment_type->date = date('Y-m-d H:i:s');
				$user_payment_type->save();
				return Crunchbutton_User_Payment_Type::o( $user_payment_type->id_user_payment_type );
			}
		}
		return false;
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