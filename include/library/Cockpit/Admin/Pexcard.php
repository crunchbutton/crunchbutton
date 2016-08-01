<?php

class Cockpit_Admin_Pexcard extends Cockpit_Admin_Pexcard_Trackchange {

	const CONFIG_KEY_PEX_AMOUNT_TO_SHIFT_START = 'pex_amount_shift_start';
	const CONFIG_KEY_PEX_SHIFT_ENABLE = 'pex_card_funds_shift_enable';
	const CONFIG_KEY_PEX_ORDER_ENABLE = 'pex_card_funds_order_enable';
	const CONFIG_KEY_PEX_ORDER_ENABLE_FOR_CASH = 'pex_card_funds_order_enable_for_cash';
	const CONFIG_KEY_PEX_BUSINESS_CARD = 'pex_business_card';
	const CONFIG_KEY_PEX_TEST_CARD = 'pex_test_card';
	const CONFIG_KEY_PEX_ACTIVE = 'pex-card-active';

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

	public static function getByAdmin( $id_admin = null ){
		if (!$id_admin) {
			return false;
		}
		return Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard WHERE id_admin = ? ORDER BY id_admin_pexcard DESC', [$id_admin]);
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

	public function removeFundsOrderRejected( $id_order ){
		if( intval( Crunchbutton_Config::getVal( Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_ORDER_ENABLE ) ) > 0 ){
			$order = Crunchbutton_Order::o( $id_order );
			if( !Crunchbutton_Pexcard_Action::checkOrderReturnedFunds( $id_order, $this->id_admin ) ){
				if( ( $order->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD ) ||
						intval( Crunchbutton_Config::getVal( Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_ORDER_ENABLE_FOR_CASH ) ) > 0 ){
					$amount = number_format( floatval( $order->price + $order->tax() ), 2 );
					$amount = $amount * -1;
					return $this->addFunds( [ 'action' => Crunchbutton_Pexcard_Action::ACTION_ORDER_REJECTED, 'id_order' => $id_order, 'amount' => $amount ] );
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

	public function isBusinessCard(){
		$businessCardList = Cockpit_Admin_Pexcard::businessCardList();
		foreach ( $businessCardList as $card) {
			if( $card == intval( $this->card_serial ) ){
				return true;
			}
		}
		return false;
	}

	public function isTestCard(){
		$testCardList = Cockpit_Admin_Pexcard::testCardList();
		foreach ( $testCardList as $card) {
			if( $card == intval( $this->card_serial ) ){
				return true;
			}
		}
		return false;
	}

	public static function pexCardRemoveCardFundsDaily(){
		$cards = Crunchbutton_Pexcard_Details::cards();
		$removeFrom = [];
		foreach( $cards as $card ){
			if( $card->LedgerBalance || $card->AvailableBalance ){
				$removeFrom[ $card->id ] = true;
			}
		}
		$cards = Cockpit_Admin_Pexcard::q( 'SELECT DISTINCT( ap.id_pexcard ) AS id_pexcard FROM pexcard_action pa INNER JOIN admin_pexcard ap ON ap.id_admin_pexcard = pa.id_admin_pexcard WHERE DATE( pa.date ) >= DATE( NOW() - INTERVAL 10 DAY ) ORDER BY id_pexcard_action DESC' );
		foreach( $cards as $card ){
			$removeFrom[ $card->id_pexcard ] = true;
		}
		$cards = array_keys( $removeFrom );
		$cards = join( $cards, ',' );
		$cards = self::q( 'SELECT * FROM admin_pexcard WHERE id_pexcard IN ( ' . $cards . ' )' );
		foreach( $cards as $card ){
			if( !$card->isBusinessCard() ){
				$card->createQueRemoveFunds();
			}
		}
	}

	public function createQueRemoveFunds(){
		echo "creating remove funds que # $this->id_admin_pexcard \n";
		$info = json_encode( [ 'id_admin_pexcard' => $this->id_admin_pexcard ] );
		$q = Queue::create([
			'type' => Crunchbutton_Queue::TYPE_PEXCARD_REMOVE_FUNDS,
			'info' => $info
		]);
	}

	public function runQueRemoveFunds(){
		if( $this->isBusinessCard() ){
			return;
		}
		echo "running remove funds que # $this->id_admin_pexcard \n";
		$pexcard_action = new Crunchbutton_Pexcard_Action();
		$pexcard_action->id_admin_pexcard = $this->id_admin_pexcard;
		$pexcard_action->date = date( 'Y-m-d H:i:s' );
		$pexcard_action->tries = 0;
		$pexcard_action->action = Crunchbutton_Pexcard_Action::ACTION_ZERO;
		$pexcard_action->status = Crunchbutton_Pexcard_Action::STATUS_SCHEDULED;
		$pexcard_action->save();
		$pexcard_action->run();
	}

	// deprecated
	public function pexCardRemoveLeftFunds( $amount ){
		if( $this->isBusinessCard() ){
			return;
		}
		if( $amount ){
			$amount = $amount * -1;
			return $this->addFunds( [ 'action' => Crunchbutton_Pexcard_Action::ACTION_REMOVE_FUNDS, 'amount' => $amount, 'run' => true ] );
		} else {
			$card = $this->load_card_info();
			if( $card && $card->availableBalance && floatval( $card->availableBalance ) > 0 ){
				$amount = $card->availableBalance;
				$amount = $amount * -1;
				return $this->addFunds( [ 'action' => Crunchbutton_Pexcard_Action::ACTION_REMOVE_FUNDS, 'amount' => $amount, 'run' => true ] );
			}
		}
	}

	public function removeFundsShiftFinished( $id_admin_shift_assign ){
		// #4281
		if( $this->isBusinessCard() ){
			return;
		}
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
				if( $id_admin_shift_assign && !Crunchbutton_Pexcard_Action::checkShiftReceivedFunds( $id_admin_shift_assign ) ){
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

		if( !$this->isPexCardFundsActive() ){
			return false;
		}

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
					case Crunchbutton_Pexcard_Action::ACTION_ORDER_REJECTED:
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

				if( $params[ 'run' ] ){
					$pexcard_action->run();
				} else {
					$pexcard_action->que();
				}

				return $pexcard_action;
			}
		} else {
			return false;
		}
	}

	public function getByPexcard( $id_pexcard ){
		$admin_pexcard = Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard WHERE id_pexcard = ? LIMIT 1', [$id_pexcard]);
		if( $admin_pexcard->id_admin_pexcard ){
			return $admin_pexcard;
		} else {
			$_card = Crunchbutton_Pexcard_Card::details( $id_pexcard );
			$admin_pexcard = new Cockpit_Admin_Pexcard;
			$admin_pexcard->id_pexcard = $id_pexcard;
			if( $_card->body ){
				$_card = $_card->body;
				$admin_pexcard->last_four = $_card->cards[ 0 ]->cardNumber;
				$admin_pexcard->card_serial = $_card->lastName;
				$admin_pexcard->save();
			} else {
				$admin_pexcard->last_four = null;
				$admin_pexcard->card_serial = null;;
			}
		}
		return $admin_pexcard;
	}

	public function getByPexCardId( $id_pexcard ){
		$admin_pexcard = Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard WHERE id_pexcard = ? LIMIT 1', [$id_pexcard]);
		if( $admin_pexcard->id_admin_pexcard ){
			return $admin_pexcard;
		}
		return false;
	}

	public function getByCardSerial( $card_serial ){
		$admin_pexcard = Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard WHERE card_serial = ? LIMIT 1', [$card_serial]);
		if( $admin_pexcard->id_admin_pexcard ){
			return $admin_pexcard;
		}
		return false;
	}

	public function businessCardList(){
		$cards = [];
		$configs = Crunchbutton_Config::q( "SELECT * FROM config c INNER JOIN admin_pexcard ap ON c.value = ap.card_serial WHERE c.`key` = ?", [Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_BUSINESS_CARD]);
		foreach ( $configs as $config ) {
			$cards[] = intval( $config->value );
		}
		return $cards;
	}

	public function testCardList(){
		$cards = [];
		$configs = Crunchbutton_Config::q( "SELECT * FROM config WHERE `key` = ?" ,[Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_TEST_CARD]);
		foreach ( $configs as $config ) {
			$cards[] = intval( $config->value );
		}
		return $cards;
	}

	public function isPexCardFundsActive(){
		return Crunchbutton_Config::getVal(self::CONFIG_KEY_PEX_ACTIVE) ? true : false;
	}

	public function removeOldAssignments( $id_admin ){
		$pexcards = self::q( 'SELECT * FROM admin_pexcard WHERE id_admin = ?', [$id_admin] );
		foreach($pexcards as $pexcard){
			$pexcard->id_admin = null;
			$pexcard->save();
		}
		return true;
	}

	public function loadSettings(){
		if( !$this->_config ){
			$configs = Crunchbutton_Config::q( "SELECT * FROM config WHERE `key` LIKE 'pex_%' ORDER BY value ASC" );
			$this->_config = [ 'cards' => [ 'business' => [], 'test' => [] ] ];
			foreach ( $configs as $config ) {

				switch ($config->key) {
					case self::CONFIG_KEY_PEX_SHIFT_ENABLE:
					case self::CONFIG_KEY_PEX_ORDER_ENABLE:
					case self::CONFIG_KEY_PEX_ORDER_ENABLE_FOR_CASH:
					case self::CONFIG_KEY_PEX_ACTIVE:
						$config->value = $config->value ? true : false;
						break;
					case self::CONFIG_KEY_PEX_BUSINESS_CARD:
						$this->_config[ 'cards' ][ 'business' ][] = [ 'id_config' => intval( $config->id_config ), 'value' => intval( $config->value ) ];
						break;
					case self::CONFIG_KEY_PEX_TEST_CARD:
						$this->_config[ 'cards' ][ 'test' ][] = [ 'id_config' => intval( $config->id_config ), 'value' => intval( $config->value ) ];
						break;
				}

				$this->_config[ $config->key ] = $config->value;
			}
			unset( $this->_config[ Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_BUSINESS_CARD ] );
			unset( $this->_config[ Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_TEST_CARD ] );
		}
		return $this->_config;
	}

}