<?php

class Cockpit_Admin_Pexcard extends Cana_Table {

	const CONFIG_KEY_PEX_AMOUNT_TO_SHIFT_START = 'pex-amount-shift-start';
	const CONFIG_KEY_PEX_SHIFT_ENABLE = 'pex-card-funds-shift-enable';
	const CONFIG_KEY_PEX_ORDER_ENABLE = 'pex-card-funds-order-enable';
	const CONFIG_KEY_PEX_ORDER_ENABLE_FOR_CASH = 'pex-card-funds-order-enable-for-cash';

	public function __construct($id = null) {
		parent::__construct();
		$this->table( 'admin_pexcard' )->idVar( 'id_admin_pexcard' )->load( $id );
	}

	public function admin(){
		if( !$this->_admin && $this->id_admin ){
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function pexcard(){
		if( !$this->_pexcard ){
			$this->_pexcard = $this->load_card_info();
		}
		return $this->_pexcard;
	}

	public function load_card_info(){
		if( $this->id_pexcard ){
			$card = Crunchbutton_Pexcard_Card::details( $this->id_pexcard );
			if( $card->body && $card->body->id ){
				return $card->body;
			}
		}
		return false;
	}

	public function actions(){
		return Crunchbutton_Pexcard_Action::actionsByCard( $this->id_admin_pexcard );
	}

	public function getByAdmin( $id_admin ){
		return Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard WHERE id_admin = "' . $id_admin . '"' );
	}

	public function removeFundsOrderCancelled( $id_order ){
		if( intval( Crunchbutton_Config::getVal( Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_ORDER_ENABLE ) ) > 0 ){
			$order = Crunchbutton_Order::o( $id_order );
			if( !Crunchbutton_Pexcard_Action::checkOrderReturnedFunds( $id_order, $this->id_admin ) ){
				if( ( $order->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD ) ||
						intval( Crunchbutton_Config::getVal( Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_ORDER_ENABLE_FOR_CASH ) ) > 0 ){
					$amount = number_format( floatval( $order->price + $order->tax() ), 2 );
					$amount = $amount * -1;
					return $this->addFunds( [ 'action' => Crunchbutton_Pexcard_Action::ACTION_ORDER_CANCELLED, 'id_order' => $id_order, 'amount' => $amount ] );
				}
			}
		}
	}

	public function addFundsOrderAccepeted( $id_order ){
		if( intval( Crunchbutton_Config::getVal( Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_ORDER_ENABLE ) ) > 0 ){
			$order = Crunchbutton_Order::o( $id_order );
			if( floatval( Crunchbutton_Pexcard_Action::checkOrderReceivedFunds( $id_order, $this->id_admin ) ) == 0 ){
				if( ( $order->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD ) ||
						intval( Crunchbutton_Config::getVal( Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_ORDER_ENABLE_FOR_CASH ) ) > 0 ){
					$amount = number_format( floatval( $order->price + $order->tax() ), 2 );
					return $this->addFunds( [ 'action' => Crunchbutton_Pexcard_Action::ACTION_ORDER_ACCEPTED, 'id_order' => $id_order, 'amount' => $amount ] );
				}
			}
		}
	}

	public function removeFundsShiftFinished( $id_admin_shift_assign ){
		if( intval( Crunchbutton_Config::getVal( Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_SHIFT_ENABLE ) ) > 0 ){
			if( !Crunchbutton_Pexcard_Action::checkShiftReturnedFunds( $id_admin_shift_assign ) ){
				$card = $this->load_card_info();
				if( $card && $card->availableBalance && floatval( $card->availableBalance ) > 0 ){
					$amount = $card->availableBalance;
					$amount = $amount * -1;
					return $this->addFunds( [ 'action' => Crunchbutton_Pexcard_Action::ACTION_SHIFT_FINISHED, 'id_admin_shift_assign' => $id_admin_shift_assign, 'amount' => $amount ] );
				}
			}
		}
	}

	public function addShiftStartFunds( $id_admin_shift_assign ){
		if( intval( Crunchbutton_Config::getVal( Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_SHIFT_ENABLE ) ) > 0 ){
			$config = Crunchbutton_Config::getConfigByKey( Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_AMOUNT_TO_SHIFT_START );
			if( $config->value ){
				// Make sure the haven't received funds yet
				if( !Crunchbutton_Pexcard_Action::checkShiftReceivedFunds( $id_admin_shift_assign ) ){
					$amount = number_format( floatval( $config->value ), 2 );
					return $this->addFunds( [ 'action' => Crunchbutton_Pexcard_Action::ACTION_SHIFT_STARTED, 'id_admin_shift_assign' => $id_admin_shift_assign, 'amount' => $amount ] );
				}
			}
		}
	}

	public function addArbitraryFunds( $amount, $note ){
		return $this->addFunds( [ 'action' => Crunchbutton_Pexcard_Action::ACTION_ARBRITARY, 'note' => $note, 'amount' => $amount ] );
	}

	public function addFunds( $params ){
		$add = false;
		// for tests allows just daniel and david's cards
		if( intval( $this->id_pexcard ) == 100254 || ( intval( $this->id_pexcard ) == 100296 ) || ( $params[ 'action' ] == Crunchbutton_Pexcard_Action::ACTION_ARBRITARY ) ){
			$add = true;
		}
		if( $add ){

			$card = $this->pexcard();
			// Check if the card could receive funds
			if( ( ( $card->ledgerBalance + $params[ 'amount' ] ) > Crunchbutton_Pexcard_Monitor::BALANCE_LIMIT ) ||
					( $params[ 'amount' ] > Crunchbutton_Pexcard_Monitor::TRANSFER_LIMIT ) ){
				$this->_error = Crunchbutton_Pexcard_Monitor::balancedExcededLimit( $card, $params[ 'amount' ], $params[ 'note' ] );
				return false;
			}

			$action = ( !$params[ 'action' ] ) ? Crunchbutton_Pexcard_Action::ACTION_ARBRITARY : $params[ 'action' ];
			if( $this->id_pexcard ){
				$amount = $params[ 'amount' ];

				if( floatval( $amount ) != 0 ){
					$pexcard_action = new Crunchbutton_Pexcard_Action();
					switch ( $params[ 'action' ] ) {
						case Crunchbutton_Pexcard_Action::ACTION_SHIFT_STARTED:
						case Crunchbutton_Pexcard_Action::ACTION_SHIFT_FINISHED:
							$pexcard_action->id_admin_shift_assign = $params[ 'id_admin_shift_assign' ];
							break;
						case Crunchbutton_Pexcard_Action::ACTION_ORDER_ACCEPTED:
						case Crunchbutton_Pexcard_Action::ACTION_ORDER_CANCELLED:
							$pexcard_action->id_order = $params[ 'id_order' ];
							break;
						default:
							$pexcard_action->id_admin = c::user()->id_admin;
							break;
					}
					$pexcard_action->amount = $amount;
					if( $pexcard_action->amount > 0 ){
						$pexcard_action->type = Crunchbutton_Pexcard_Action::TYPE_CREDIT;
					} else {
						$pexcard_action->type = Crunchbutton_Pexcard_Action::TYPE_DEBIT;
					}
					$pexcard_action->id_admin_pexcard = $this->id_admin_pexcard;
					$pexcard_action->id_driver = $this->id_admin;
					$pexcard_action->date = date( 'Y-m-d H:i:s' );
					$pexcard_action->note = $params[ 'note' ];
					$pexcard_action->tries = 0;
					$pexcard_action->action = $action;
					$pexcard_action->status = Crunchbutton_Pexcard_Action::STATUS_SCHEDULED;
					$pexcard_action->save();
					$pexcard_action = Crunchbutton_Pexcard_Action::o( $pexcard_action->id_pexcard_action );

					$pexcard_action->que();

					return $pexcard_action;
				}
			} else {
				return false;
			}
		}
	}

	public function old_addFunds( $params ){
		$add = false;
		// for tests allows just daniel and david's cards
		if( intval( $this->id_pexcard ) == 100254 || ( intval( $this->id_pexcard ) == 100296 ) || ( $params[ 'action' ] == Crunchbutton_Pexcard_Action::ACTION_ARBRITARY ) ){
			$add = true;
		}
		if( $add ){

			$card = $this->pexcard();
			// Check if the card could receive funds
			if( ( ( $card->ledgerBalance + $params[ 'amount' ] ) > Crunchbutton_Pexcard_Monitor::BALANCE_LIMIT ) ||
					( $params[ 'amount' ] > Crunchbutton_Pexcard_Monitor::TRANSFER_LIMIT ) ){
				$this->_error = Crunchbutton_Pexcard_Monitor::balancedExcededLimit( $card, $params[ 'amount' ], $params[ 'note' ] );
				return false;
			}

			$action = ( !$params[ 'action' ] ) ? Crunchbutton_Pexcard_Action::ACTION_ARBRITARY : $params[ 'action' ];
			if( $this->id_pexcard ){
				$amount = $params[ 'amount' ];
				if( floatval( $amount ) != 0 ){
					$card = Crunchbutton_Pexcard_Card::fund( $this->id_pexcard, $amount );
				}

				if( $card->body && $card->body->id ){
					$action = new Crunchbutton_Pexcard_Action();
					switch ( $params[ 'action' ] ) {
						case Crunchbutton_Pexcard_Action::ACTION_SHIFT_STARTED:
						case Crunchbutton_Pexcard_Action::ACTION_SHIFT_FINISHED:
							$action->id_admin_shift_assign = $params[ 'id_admin_shift_assign' ];
							break;
						case Crunchbutton_Pexcard_Action::ACTION_ORDER_ACCEPTED:
						case Crunchbutton_Pexcard_Action::ACTION_ORDER_CANCELLED:
							$action->id_order = $params[ 'id_order' ];
							break;
						default:
							$action->id_admin = c::user()->id_admin;
							break;
					}
					$action->amount = $amount;
					if( $action->amount > 0 ){
						$action->type = Crunchbutton_Pexcard_Action::TYPE_CREDIT;
					} else {
						$action->type = Crunchbutton_Pexcard_Action::TYPE_DEBIT;
					}
					$action->id_admin_pexcard = $this->id_admin_pexcard;
					$action->id_driver = $this->id_admin;
					$action->date = date( 'Y-m-d H:i:s' );
					$action->note = $params[ 'note' ];
					$action->response = json_encode( $card->body );
					$action->save();
					$action = Crunchbutton_Pexcard_Action::o( $action->id_pexcard_action );
					return $action;
				} else {
					$message = 'Pexcard funds error: ' . $card->Message . "\n";
					$message .= 'Amount: ' . $params[ 'amount' ] . "\n";
					$message .= 'Action: ' . $params[ 'action' ] . "\n";
					$message .= 'Card Serial: ' . $this->card_serial . "\n";
					$message .= 'Last four: ' . $this->last_four;
					Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );
				}
			} else {
				return false;
			}
		}
	}

	public function getByPexcard( $id_pexcard ){
		$admin_pexcard = Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard WHERE id_pexcard = "' . $id_pexcard . '" LIMIT 1' );
		if( $admin_pexcard->id_admin_pexcard ){
			return $admin_pexcard;
		}
		$admin_pexcard = new Cockpit_Admin_Pexcard;
		$admin_pexcard->id_pexcard = $id_pexcard;
		return $admin_pexcard;
	}

}