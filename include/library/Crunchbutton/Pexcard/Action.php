<?php

class Crunchbutton_Pexcard_Action extends Cana_Table {

	const ACTION_SHIFT_STARTED = 'shift-started';
	const ACTION_SHIFT_FINISHED = 'shift-finished';
	const ACTION_ORDER_ACCEPTED = 'order-accepted';
	const ACTION_ORDER_CANCELLED = 'order-cancelled';
	const ACTION_ARBRITARY = 'arbritary';

	const STATUS_SCHEDULED = 'scheduled';
	const STATUS_PROCESSING = 'processing';
	const STATUS_DONE = 'done';
	const STATUS_ERROR = 'error';

	const MAX_TRIES = 1;

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

	public function actionsByDriver( $id_driver ){
		return Crunchbutton_Pexcard_Action::q( 'SELECT * FROM pexcard_action WHERE id_driver = "' . $id_driver . '" ORDER BY id_pexcard_action DESC' );
	}

	public function actionsByCard( $id_admin_pexcard ){
		return Crunchbutton_Pexcard_Action::q( 'SELECT * FROM pexcard_action WHERE id_admin_pexcard = "' . $id_admin_pexcard . '" ORDER BY id_pexcard_action DESC' );
	}

	public function checkOrderReceivedFunds( $id_order, $id_driver ){
		$received_action = Crunchbutton_Pexcard_Action::q( 'SELECT SUM( amount ) AS amount FROM pexcard_action WHERE id_order = "' . $id_order . '" AND id_driver = "' . $id_driver . '" AND type = "' . Crunchbutton_Pexcard_Action::TYPE_CREDIT . '"' );
		$returned_action = Crunchbutton_Pexcard_Action::q( 'SELECT SUM( amount ) AS amount FROM pexcard_action WHERE id_order = "' . $id_order . '" AND id_driver = "' . $id_driver . '" AND type = "' . Crunchbutton_Pexcard_Action::TYPE_DEBIT . '"' )->get( 0 );
		$returned_amount = number_format( $received_action->amount, 2 );
		$received_amount = number_format( $returned_action->amount, 2 );
		return ( ( $returned_amount + $received_amount ) > 0 );
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

	public function que( $force = false ){
		if( $force || $this->status == Crunchbutton_Pexcard_Action::STATUS_SCHEDULED ){
			$this->tries = ( !$this->tries ) ? 0 : $this->tries;
			if( $this->tries < Crunchbutton_Pexcard_Action::MAX_TRIES ){

				$this->status = Crunchbutton_Pexcard_Action::STATUS_PROCESSING;
				$this->status_date = date( 'Y-m-d H:i:s' );
				$this->tries = ( $this->tries + 1 );
				$this->save();

				$action = $this;

				// Cana::timeout( function() use( $action ) {
					$pexcard = $action->pexcard();
					try {
						$card = Crunchbutton_Pexcard_Card::fund( $pexcard->id_pexcard, $action->amount );
						if( $card->body && $card->body->id ){
							$action->status = Crunchbutton_Pexcard_Action::STATUS_DONE;
							$action->response = json_encode( $card->body );
							$action->status_date = date( 'Y-m-d H:i:s' );
							$action->save();
			 	 		} else {
			 	 			$this->error( $card->Message );
			 	 		}
					} catch ( Exception $e ) {
						$action->que();
					}  finally {
						if( $action->status != Crunchbutton_Pexcard_Action::STATUS_DONE ){
							$action->que();
						}
					}
				// } );
			} else {
				$this->error( 'Exceeded the maximum (' . Crunchbutton_Pexcard_Action::MAX_TRIES . ') tries to add funds.' );
			}
		}
	}

	public function error( $error ){
		$pexcard = $this->pexcard();
		$message = 'Pexcard funds error: ' . $error . "\n";
		$message .= 'Amount: ' . $this->amount . "\n";
		$message .= 'Action: ' . $this->action . "\n";
		$message .= 'Card Serial: ' . $pexcard->card_serial . "\n";
		$message .= 'Last four: ' . $pexcard->last_four;
		Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );
	}

	public function pexcard(){
		if( !$this->_pexcard ){
			$this->_pexcard = Cockpit_Admin_Pexcard::o( $this->id_admin_pexcard );
		}
		return $this->_pexcard;
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->timestamp, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
		}
		return $this->_date;
	}
}