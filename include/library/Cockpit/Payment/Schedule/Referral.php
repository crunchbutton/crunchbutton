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
		$query = 'SELECT * FROM payment_schedule_referral WHERE id_referral = "' . $id_referral . '" LIMIT 1';
		$referral = Cockpit_Payment_Schedule_Referral::q( $query );
		if( $referral->id_payment_schedule_referral ){
			return $referral;
		}
		return false;
	}
}