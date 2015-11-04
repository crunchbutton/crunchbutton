<?php

class Cockpit_Payment_Schedule_Referral extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this->table('payment_schedule_referral')->idVar('id_payment_schedule_referral')->load($id);
	}

	public function payment_schedule() {
		return Cockpit_Payment_Schedule::o($this->id_payment_schedule);
	}

	public function referral(){
		if( !$this->_referral ){
			$this->_referral = Crunchbutton_Referral::o( $this->id_referral );
		}
		return $this->_referral;
	}

	public function payment(){
		$payment = $this->payment_schedule()->payment();
		if( $payment->id_payment ){
			return $payment;
		}
		return false;
	}

	public function checkReferralWasPaidDriver( $id_referral ){
		$query = 'SELECT * FROM payment_schedule_referral INNER JOIN payment_schedule ON payment_schedule.id_payment_schedule = payment_schedule_referral.id_payment_schedule WHERE id_referral = ? AND payment_schedule.status != ? LIMIT 1';
		$referral = Cockpit_Payment_Schedule_Referral::q( $query, [$id_referral, Cockpit_Payment_Schedule::STATUS_REVERSED]);
		if( $referral->id_payment_schedule_referral ){
			return $referral;
		}
		return false;
	}
}