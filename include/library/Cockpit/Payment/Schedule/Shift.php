<?php

class Cockpit_Payment_Schedule_Shift extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this->table('payment_schedule_shift')->idVar('id_payment_schedule_shift')->load($id);
	}

	public function payment_schedule() {
		return Cockpit_Payment_Schedule::o($this->id_payment_schedule);
	}

	public function shift_assign(){
		return Crunchbutton_Admin_Shift_Assign::o( $this->id_admin_shift_assign );
	}

	public function shift(){
		return $this->shift_assign()->shift();
	}

	public function payment(){
		$payment = $this->payment_schedule()->payment();
		if( $payment->id_payment ){
			return $payment;
		}
		return false;
	}

	public static function checkShiftWasPaidDriver( $id_admin_shift_assign ){
		$query = 'SELECT * FROM payment_schedule_shift INNER JOIN payment_schedule ON payment_schedule.id_payment_schedule = payment_schedule_shift.id_payment_schedule WHERE id_admin_shift_assign = ? AND payment_schedule.status != ? LIMIT 1';
		$shift = Cockpit_Payment_Schedule_Shift::q( $query, [$id_admin_shift_assign, Cockpit_Payment_Schedule::STATUS_REVERSED]);
		if( $shift->id_payment_schedule_shift ){
			return $shift;
		}
		return false;
	}
}