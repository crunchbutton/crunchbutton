<?php

class Crunchbutton_Reward extends Cana_Table{

	const CONFIG_KEY_POINTS_PER_CENTS_VALUE = 'reward_points_per_cents_value';
	const CONFIG_KEY_POINTS_PER_CENTS_OPERATION = 'reward_points_per_cents_operation';

	const CONFIG_KEY_SHARED_ORDER_VALUE = 'reward_points_shared_order_value';
	const CONFIG_KEY_SHARED_ORDER_OPERATION = 'reward_points_shared_order_operation';

	const CONFIG_KEY_ORDER_VALUE_OVER_AMOUNT = 'reward_points_order_value_over_amount';
	const CONFIG_KEY_ORDER_VALUE_OVER_VALUE = 'reward_points_order_value_over_value';
	const CONFIG_KEY_ORDER_VALUE_OVER_OPERATION = 'reward_points_order_value_over_operation';

	const CONFIG_KEY_GET_REFERED_VALUE = 'reward_points_get_refered_value';

	const CONFIG_KEY_REFER_NEW_USER_VALUE = 'reward_points_refer_new_user_value';
	const CONFIG_KEY_WIN_CLUCKBUTTON_VALUE = 'reward_points_win_cluckbutton_value';
	const CONFIG_KEY_MAKE_ACCOUNT_VALUE = 'reward_points_make_acount_value';
	const CONFIG_KEY_MAKE_ACCOUNT_OPERATION = 'reward_points_make_acount_operation';
	const CONFIG_KEY_ORDER_TWICE_SAME_WEEK_VALUE = 'reward_points_order_twice_same_week_value';
	const CONFIG_KEY_ORDER_TWICE_SAME_WEEK_OPERATION = 'reward_points_order_twice_same_operation';

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

	//
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

	//
	public function sharedOrder( $id_order ){
		$settings = $this->loadSettings();
		$points = $this->processOrder( $id_order );
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_SHARED_ORDER_VALUE ],
																		$settings[ Crunchbutton_Reward::CONFIG_KEY_SHARED_ORDER_OPERATION ],
																		$points );
	}

	//
	public function getRefered(){
		$settings = $this->loadSettings();
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_GET_REFERED_VALUE ], '+', 0 );
	}

	//
	public function getReferNewUser(){
		$settings = $this->loadSettings();
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_REFER_NEW_USER_VALUE ], '+', 0 );
	}

	//
	public function makeAccountAfterOrder( $id_user ){
		$user = Crunchbutton_User::o( $id_user );
		if( $user->id_user ){
			$order = $user->lastOrder();
			if( $order->id_order ){
				$settings = $this->loadSettings();
				$points = $this->processOrder( $order->id_order );
				return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_MAKE_ACCOUNT_VALUE ],
																				$settings[ Crunchbutton_Reward::CONFIG_KEY_MAKE_ACCOUNT_OPERATION ],
																				$points );
			}
		}
		return 0;
	}

	//
	public function orderTwiceSameWeek( $id_user ){
		$query = "SELECT o.*, DATE_FORMAT( o.date, '%Y%U' ) week FROM `order` o WHERE o.id_user = '" . $id_user . "' ORDER BY id_order DESC LIMIT 2";
		$orders = Crunchbutton_Order::q( $query );
		if( $orders->count() == 2  ){
			$order_1 = $orders->get( 0 );
			$order_2 = $orders->get( 1 );
			if( $order_1->week == $order_2->week ){
				$settings = $this->loadSettings();
				$points = $this->processOrder( $order_1->id_order );
				return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_TWICE_SAME_WEEK_VALUE ],
																				$settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_TWICE_SAME_WEEK_OPERATION ],
																				$points );
			}
		}
		return 0;
	}

	//
	public function winCluckbutton(){
		$settings = $this->loadSettings();
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_WIN_CLUCKBUTTON_VALUE ], '+', 0 );
	}

	private function calcPointsPerCents( $cents ){
		$settings = $this->loadSettings();
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_POINTS_PER_CENTS_VALUE ],
																		$settings[ Crunchbutton_Reward::CONFIG_KEY_POINTS_PER_CENTS_OPERATION ],
																		$cents );
	}

	private function calcOrdersOver( $cents, $points ){
		$settings = $this->loadSettings();
		$value = ( floatval( $settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_VALUE_OVER_AMOUNT ] ) * 100 );
		if( $cents > $value ){
			return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_VALUE_OVER_VALUE ],
																			$settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_VALUE_OVER_OPERATION ],
																			$points );
		}
		return $points;
	}

	private function parseConfigValue( $value, $operation, $points ){
		if( $value ){
			switch ( $operation ) {
				case '+':
					return floatval( $value ) + floatval( $points );
					break;
				case '*':
					return floatval( $value ) * floatval( $points );
					break;
			}
		}
		return floatval( $points );
	}

	public function loadSettings(){
		if( !$this->_config ){
			$configs = Crunchbutton_Config::q( "SELECT * FROM config WHERE `key` LIKE 'reward_points%'" );
			foreach ( $configs as $config ) {
				$this->_config[ $config->key ] = $config->value;
			}
		}
		return $this->_config;
	}
}