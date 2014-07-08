<?php

class Cockpit_Payment_Schedule_Order extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this->table('payment_schedule_order')->idVar('id_payment_schedule_order')->load($id);
	}
	public function payment_schedule() {
		return Cockpit_Payment_Schedule::o($this->id_payment_schedule);
	}

	public function checkOrderWasPaidRestaurant( $id_order ){
		$query = 'SELECT * FROM payment_schedule_order pso
								INNER JOIN payment_schedule ps ON ps.id_payment_schedule = pso.id_payment_schedule AND ps.type = "' . Cockpit_Payment_Schedule::TYPE_RESTAURANT . '" AND ps.pay_type = "' . Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT . '"
							WHERE pso.id_order = "' . $id_order . '" LIMIT 1';
		$order = Cockpit_Payment_Schedule_Order::q( $query );
		if( $order->id_payment_schedule_order ){
			return true;
		}
		return false;
	}

	public function checkOrderWasReimbursedDriver( $id_order ){
		$query = 'SELECT * FROM payment_schedule_order pso
								INNER JOIN payment_schedule ps ON pso.id_payment_schedule = pso.id_payment_schedule AND ps.type = "' . Cockpit_Payment_Schedule::TYPE_DRIVER . '" AND ps.pay_type = "' . Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT . '"
							WHERE pso.id_order = "' . $id_order . '" LIMIT 1';
		$order = Cockpit_Payment_Schedule_Order::q( $query );
		if( $order->id_payment_schedule_order ){
			return true;
		}
		return false;
	}

	public function checkOrderWasPaidDriver( $id_order ){
		$query = 'SELECT * FROM payment_schedule_order pso
								INNER JOIN payment_schedule ps ON pso.id_payment_schedule = pso.id_payment_schedule AND ps.type = "' . Cockpit_Payment_Schedule::TYPE_DRIVER . '" AND ps.pay_type = "' . Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT . '"
							WHERE pso.id_order = "' . $id_order . '" LIMIT 1';
		$order = Cockpit_Payment_Schedule_Order::q( $query );
		if( $order->id_payment_schedule_order ){
			return true;
		}
		return false;
	}

	public function order() {
		return Cockpit_Order::o( $this->id_order );
	}
}