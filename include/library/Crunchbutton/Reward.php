<?php

class Crunchbutton_Reward extends Cana_Table{

	const CONFIG_KEY_POINTS_PER_CENTS = 'reward-points-per-cents';
	const CONFIG_KEY_ORDER_VALUE_OVER_VALUE = 'reward-points-order-value-over-value';
	const CONFIG_KEY_ORDER_VALUE_OVER_ACTION = 'reward-points-order-value-over-action';
	const CONFIG_KEY_SHARED_ORDER = 'reward-points-shared-order';
	const CONFIG_KEY_GET_REFERED = 'reward-points-get-refered';
	const CONFIG_KEY_REFER_NEW_USER = 'reward-points-refer-new-user';
	const CONFIG_KEY_WIN_CLUCKBUTTON = 'reward-points-win-cluckbutton';
	const CONFIG_KEY_MAKE_ACCOUNT_AFTER_ORDER = 'reward-points-make-acount-after-order';
	const CONFIG_KEY_ORDER_TWICE_SAME_WEEK = 'reward-points-order-twice-same-week';

	public function saveReward( $params ){
		$credit = new Crunchbutton_Credit();
		$credit->id_user = $params[ 'id_user' ];
		$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
		$credit->date = date( 'Y-m-d H:i:s' );
		$credit->value = $params[ 'points' ];
		$credit->id_order = $params[ 'id_order' ];
		$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_POINT;
		$credit->note = $params[ 'note' ];
		$credit->save();
	}

	// Check if the user already received points for sharing this order
	public function orderWasAlreadyShared( $id_order ){
		$credit = Crunchbutton_Credit::q( 'SELECT * FROM credit c WHERE c.id_order = "' . $id_order . '" AND c.type = "' . Crunchbutton_Credit::TYPE_CREDIT . '" AND credit_type = "' . Crunchbutton_Credit::CREDIT_TYPE_POINT . '" AND note LIKE "%sharing%" LIMIT 1' );
		if( $credit->id_credit ){
			return true;
		}
		return false;
	}

	public function processOrder( $id_order ){
		$order = Crunchbutton_Order::o( $id_order );
		$this->loadSettings();
		if( $order->id_order ){
			$amount = number_format( $order->final_price_plus_delivery_markup, 2 );
			$cents = $amount * 100;
			$points = $this->calcPointsPerCents( $cents );
			$points = $this->calcOrdersOver( $cents, $points );
			return $points;
		}
		return 0;
	}

	public function sharedOrder( $id_order ){
		$settings = $this->loadSettings();
		$points = $this->processOrder( $id_order );
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_SHARED_ORDER ], $points );
	}

	public function getRefered(){
		$settings = $this->loadSettings();
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_GET_REFERED ], 0 );
	}

	public function getReferNewUser(){
		$settings = $this->loadSettings();
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_REFER_NEW_USER ], 0 );
	}

	public function makeAccountAfterOrder( $id_user ){
		$user = Crunchbutton_User::o( $id_user );
		if( $user->id_user ){
			$order = $user->lastOrder();
			if( $order->id_order ){
				$settings = $this->loadSettings();
				$points = $this->processOrder( $order->id_order );
				return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_MAKE_ACCOUNT_AFTER_ORDER ], $points );
			}
		}
		return 0;
	}

	public function orderTwiceSameWeek( $id_user ){
		$query = "SELECT o.*, DATE_FORMAT( o.date, '%Y%U' ) week FROM `order` o WHERE o.id_user = '" . $id_user . "' ORDER BY id_order DESC LIMIT 2";
		$orders = Crunchbutton_Order::q( $query );
		if( $orders->count() == 2  ){
			$order_1 = $orders->get( 0 );
			$order_2 = $orders->get( 1 );
			if( $order_1->week == $order_2->week ){
				$settings = $this->loadSettings();
				$points = $this->processOrder( $order_1->id_order );
				return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_TWICE_SAME_WEEK ], $points );
			}
		}
		return 0;
	}

	public function winCluckbutton(){
		$settings = $this->loadSettings();
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_WIN_CLUCKBUTTON ], 0 );
	}

	private function calcPointsPerCents( $cents ){
		$settings = $this->loadSettings();
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_POINTS_PER_CENTS ], $cents );
	}

	private function calcOrdersOver( $cents, $points ){
		$settings = $this->loadSettings();
		$value = ( floatval( $settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_VALUE_OVER_VALUE ] ) * 100 );
		if( $cents > $value ){
			return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_VALUE_OVER_ACTION ], $points );
		}
		return $points;
	}

	private function parseConfigValue( $value, $points ){
		if( $value ){
			switch ( $value[0] ) {
				case '+':
					$amount = substr( $value, 1, strlen( $value ) );
					return floatval( $amount ) + floatval( $points );
					break;
				case '*':
					$amount = substr( $value, 1, strlen( $value ) );
					return floatval( $amount ) * floatval( $points );
					break;
			}
		}
		return floatval( $points );
	}

	private function loadSettings(){
		if( !$this->_config ){
			$configs = Crunchbutton_Config::q( "SELECT * FROM config WHERE `key` LIKE 'reward-points%'" );
			foreach ( $configs as $config ) {
				$this->_config[ $config->key ] = $config->value;
			}
		}
		return $this->_config;
	}
}