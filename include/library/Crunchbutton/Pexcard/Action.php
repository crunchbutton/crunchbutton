<?php

class Crunchbutton_Pexcard_Action extends Cana_Table {

	const ACTION_SHIFT_STARTED = 'shift-started';
	const ACTION_SHIFT_FINISHED = 'shift-finished';
	const ACTION_ORDER_ACCEPTED = 'order-accepted';
	const ACTION_ORDER_CANCELLED = 'order-cancelled';
	const ACTION_ARBRITARY = 'arbritary';

	const TYPE_CREDIT = 'credit';
	const TYPE_DEBIT = 'debit';

	public function __construct($id = null) {
		parent::__construct();
		$this->table( 'pexcard_action' )->idVar( 'id_pexcard_action' )->load( $id );
	}

	public function checkShiftReceivedFunds( $id_admin_shift_assign ){
		$action = Crunchbutton_Pexcard_Action::q( 'SELECT * FROM pexcard_action WHERE id_admin_shift_assign = "' . $id_admin_shift_assign . '" AND type = "' . Crunchbutton_Pexcard_Action::TYPE_CREDIT . '"' );
		return ( $action->id_pexcard_action );
	}

	public function checkShiftReturnedFunds(){
		$action = Crunchbutton_Pexcard_Action::q( 'SELECT * FROM pexcard_action WHERE id_admin_shift_assign = "' . $id_admin_shift_assign . '" AND type = "' . Crunchbutton_Pexcard_Action::TYPE_DEBIT . '"' );
		return ( $action->id_pexcard_action );
	}

	public function checkOrderReceivedFunds( $id_order, $id_driver ){
		$action = Crunchbutton_Pexcard_Action::q( 'SELECT SUM( amount ) AS amount FROM pexcard_action WHERE id_order = "' . $id_order . '" AND id_driver = "' . $id_driver . '" AND type = "' . Crunchbutton_Pexcard_Action::TYPE_CREDIT . '"' );
		return ( number_format( $action->amount, 2 ) );
	}

	public function checkOrderReturnedFunds( $id_order, $id_driver ){
		$received = Crunchbutton_Pexcard_Action::checkOrderReceivedFunds( $id_order, $id_driver );
		if( $received ){
			$action = Crunchbutton_Pexcard_Action::q( 'SELECT SUM( amount ) AS amount FROM pexcard_action WHERE id_order = "' . $id_order . '" AND id_driver = "' . $id_driver . '" AND type = "' . Crunchbutton_Pexcard_Action::TYPE_DEBIT . '"' );
			if( $action->amount ){
				$returned = number_format( $action->amount, 2 );
				if( ( $returned + $received ) == 0 ){
					return true;
				}
				// received funds and havent returned
				return false;
			}
			// received funds and havent returned
			return false;
		}
		// havent received funds
		return true;
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->timestamp, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
		}
		return $this->_date;
	}
}