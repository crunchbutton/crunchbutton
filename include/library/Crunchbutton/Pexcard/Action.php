<?php

class Crunchbutton_Pexcard_Action extends Cana_Table {

	const ACTION_SHIFT_STARTED = 'shift-started';
	const ACTION_SHIFT_FINISHED = 'shift-finished';
	const ACTION_ORDER_ACCEPTED = 'order-accepted';
	const ACTION_ORDER_CANCELLED = 'order-cancelled';
	const ACTION_ORDER_REJECTED = 'order-rejected';
	const ACTION_ARBRITARY = 'arbritary';
	const ACTION_REMOVE_FUNDS = 'remove-funds';

	const STATUS_SCHEDULED = 'scheduled';
	const STATUS_PROCESSING = 'processing';
	const STATUS_DONE = 'done';
	const STATUS_ERROR = 'error';

	const MAX_TRIES = 3;

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
		$received_action = Crunchbutton_Pexcard_Action::q( 'SELECT SUM( amount ) AS amount FROM pexcard_action WHERE id_order = "' . $id_order . '" AND id_driver = "' . $id_driver . '" AND type = "' . Crunchbutton_Pexcard_Action::TYPE_CREDIT . '"' )->get( 0 );
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

	public function monitor(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '-5 minutes' );
		$fiveMinutesAgo = $now->format( 'Y-m-d H:i:s' );
		$actions = Crunchbutton_Pexcard_Action::q("SELECT * FROM pexcard_action WHERE status = '" . Crunchbutton_Pexcard_Action::STATUS_PROCESSING . "' AND status_date < '" . $fiveMinutesAgo . "'");
		foreach( $actions as $action ){
			$action->status = Crunchbutton_Pexcard_Action::STATUS_ERROR;
			$action->status_date = date( 'Y-m-d H:i:s' );
			$action->response = 'Error trying to add fund!';
			$action->save();
			$action->error( $action->response );
		}
	}

	public function que(){
		$info = json_encode( [ 'id_pexcard_action', $this->id_pexcard_action ] );
		$q = Queue::create( [
			'type' => Crunchbutton_Queue::TYPE_PEXCARD_ACTION,
			'id_pexcard_action' => $this->id_pexcard_action,
			'info' => $info
		] );
	}

	public function run( $force = false ){

		echo '### running ' . $this->id_pexcard_action . "\n";
		echo '### status ' . $this->status . "\n";
		echo '### tries ' . $this->tries . "\n";
		echo '### amount ' . $this->amount . "\n";
		if( $force || $this->status == Crunchbutton_Pexcard_Action::STATUS_SCHEDULED ){
			$this->tries = ( !$this->tries ) ? 0 : $this->tries;
			if( $this->tries < Crunchbutton_Pexcard_Action::MAX_TRIES ){
				$this->status = Crunchbutton_Pexcard_Action::STATUS_PROCESSING;
				$this->status_date = date( 'Y-m-d H:i:s' );
				$this->tries = ( $this->tries + 1 );
				$this->save();
				$id_pexcard_action = $this->id_pexcard_action;
				echo '### status ' . $this->status . "\n";
				$pexcard = Cockpit_Admin_Pexcard::o( $this->id_admin_pexcard );
				try {
					$card = Crunchbutton_Pexcard_Card::fund( $pexcard->id_pexcard, $this->amount );
					echo '### $card->body->id ' . $card->body->id . "\n";
					echo '### $card->body->AccountId ' . $card->body->AccountId . "\n";
					if( $card->body && ( $card->body->id || $card->body->AccountId ) ){
						$this->status = Crunchbutton_Pexcard_Action::STATUS_DONE;
						$this->response = json_encode( $card->body );
						$this->status_date = date( 'Y-m-d H:i:s' );
						$this->save();
		 	 		} else {
		 	 			echo '### $card->Message ' . $card->Message . "\n";
		 	 			$this->error( $card->Message );
		 	 		}
				} catch ( Exception $e ) {
					echo '### $e ' . $e . "\n";
					$this->que();
				} finally {
					echo '### status ' . $this->status . "\n";
					if( $this->status != Crunchbutton_Pexcard_Action::STATUS_DONE ){
						$this->que();
					}
				}
			} else {
				$this->error( 'Exceeded the maximum (' . $action->MAX_TRIES . ') tries to add funds.' );
			}
		}
	}

	public function error( $error ){
		$pexcard = $this->pexcard();
		$message = 'Pexcard funds error: ' . $error . "\n";
		$message .= 'Amount: $' . $this->amount . "\n";
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

	public function status_date() {
		if (!isset($this->_status_date)) {
			$this->_status_date = new DateTime($this->status_date, new DateTimeZone(c::config()->timezone));
		}
		return $this->_status_date;
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
		}
		return $this->_date;
	}

	public function exports(){

		$out = $this->properties();

		$driver = Admin::o( $out[ 'id_driver' ] );
		$out[ 'driver' ] = $driver->name;
		$out[ 'login' ] = $driver->login;

		$pexcard = $this->pexcard();

		$out[ 'card_serial' ] = $pexcard->card_serial;
		$out[ 'last_four' ] = $pexcard->last_four;

		$out[ 'date_formated' ] = $this->date()->format( 'M jS Y g:i:s A T' );

		if( $out[ 'status_date' ] ){
			$out[ 'status_date_formated' ] = $this->status_date()->format( 'M jS Y g:i:s A T' );
		}

		if( $out[ 'response' ] ){
			$out[ 'response' ] = json_decode( $out[ 'response' ] );
		}

		if( $out[ 'id_admin' ] ){
			$out[ 'admin' ] = [];
			$admin = Admin::o( $out[ 'id_admin' ] );
			$out[ 'admin' ][ 'name' ] = $admin->name;
			$out[ 'admin' ][ 'login' ] = $admin->login;
		}

		if( $out[ 'id_order' ] ){
			$out[ 'order' ] = [];
			$order = Order::o( $out[ 'id_order' ] );
			$out[ 'order' ][ 'restaurant' ] = $order->restaurant()->name;
			$out[ 'order' ][ 'customer' ] = $order->name;
		}

		if( $out[ 'id_admin_shift_assign' ] ){
			$out[ 'shift' ] = [];
			$shift = Crunchbutton_Admin_Shift_Assign::o( $out[ 'id_admin_shift_assign' ] )->shift();
			$out[ 'shift' ][ 'community' ] = $shift->community()->name;
			$out[ 'shift' ][ 'period' ] = $shift->startEndToStringCommunityTz();
		}

		unset( $out[ 'date' ] );
		unset( $out[ 'status_date' ] );

		return $out;

	}

}