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
		if( !$this->_shift_assign ){
			$this->_shift_assign = Crunchbutton_Admin_Shift_Assign::o( $this->id_admin_shift_assign );
		}
		return $this->_shift_assign;
	}

	public function shift(){
		if( !$this->_shift ){
			$this->_shift = $this->shift_assign()->shift();
		}
		return $this->_shift;
	}

	public function payment(){
		$payment = $this->payment_schedule()->payment();
		if( $payment->id_payment ){
			return $payment;
		}
		return false;
	}

	public function isShiftCreatedByDriver(){
		return ( $this->shift()->id_driver ? true : false );
	}

	public static function checkShiftWasPaidDriver( $id_admin_shift_assign ){
		$query = 'SELECT * FROM payment_schedule_shift WHERE id_admin_shift_assign = ? LIMIT 1';
		$shift = Cockpit_Payment_Schedule_Shift::q( $query, [$id_admin_shift_assign]);
		if( $shift->id_payment_schedule_shift ){
			return $shift;
		}
		return false;
	}
}