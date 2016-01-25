<?php

class Crunchbutton_Reward extends Cana_Table{

	const CONFIG_KEY_POINTS_PER_CENTS_VALUE = 'reward_points_per_cents_value';
	const CONFIG_KEY_POINTS_PER_CENTS_OPERATION = 'reward_points_per_cents_operation';
	const CONFIG_KEY_SHARED_ORDER_VALUE = 'reward_points_shared_order_value';
	const CONFIG_KEY_SHARED_ORDER_OPERATION = 'reward_points_shared_order_operation';
	const CONFIG_KEY_ORDER_VALUE_OVER_AMOUNT = 'reward_points_order_value_over_amount';
	const CONFIG_KEY_ORDER_VALUE_OVER_VALUE = 'reward_points_order_value_over_value';
	const CONFIG_KEY_ORDER_VALUE_OVER_OPERATION = 'reward_points_order_value_over_operation';
	const CONFIG_KEY_GET_REFERRED_VALUE = 'reward_points_get_referred_value';
	const CONFIG_KEY_GET_REFERRED_DISCOUNT_AMOUNT = 'reward_points_get_referred_discount_amt';
	const CONFIG_KEY_REFER_NEW_USER_AMOUNT = 'reward_points_refer_new_user_amt';
	const CONFIG_KEY_REFER_NEW_USER_VALUE = 'reward_points_refer_new_user_value';
	const CONFIG_KEY_ADMIN_REFER_USER_AMOUNT = 'reward_points_admin_refer_user_amt';
	const CONFIG_KEY_WIN_CLUCKBUTTON_VALUE = 'reward_points_win_cluckbutton_value';
	const CONFIG_KEY_MAKE_ACCOUNT_VALUE = 'reward_points_make_acount_value';
	const CONFIG_KEY_MAKE_ACCOUNT_OPERATION = 'reward_points_make_acount_operation';
	const CONFIG_KEY_ORDER_TWICE_SAME_WEEK_VALUE = 'reward_points_order_twice_week_value';
	const CONFIG_KEY_ORDER_TWICE_SAME_WEEK_OPERATION = 'reward_points_order_twice_week_operation';
	const CONFIG_KEY_ORDER_2_DAYS_IN_A_ROW_VALUE = 'reward_points_order_2_days_row_value';
	const CONFIG_KEY_ORDER_2_DAYS_IN_A_ROW_OPERATION = 'reward_points_order_2_days_row_operation';
	const CONFIG_KEY_MAX_CAP_POINTS = 'reward_points_max_cap_points';

	public function checkIfItIsEligibleForFirstTimeOrder( $phone = false ){
		$user = c::user();
		if( $user->phone ){
			$orders = Order::totalOrdersByPhone( $user->phone );
			if( $orders > 0 ){
				return false;
			}
		} else if( $phone ) {
			$orders = Order::totalOrdersByPhone( $phone );
			if( $orders > 0 ){
				return false;
			}
		}
		return true;
	}

	public function validateInviteCode( $code ){
		$codes = explode( ' ' , $code );
		foreach( $codes as $code ){
			$code = trim( $code );
			// at first check if it belongs to an admin
			$admin = Crunchbutton_Admin::byInviteCode( $code );
			if( $admin->id_admin ){
				$this->code = $code;
				return [ 'id_admin' => $admin->id_admin ];
			}
			// second check if it belongs to an user
			$user = Crunchbutton_User::byInviteCode( $code );
			if( $user->id_user && $user->active ){
				$this->code = $code;
				return [ 'id_user' => $user->id_user ];
			}
		}
		return false;
	}

	public function saveReward( $params ){
		$credit = new Crunchbutton_Credit();
		$credit->id_user = $params[ 'id_user' ];
		$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
		$credit->date = date( 'Y-m-d H:i:s' );
		$credit->value = $params[ 'points' ];
		$credit->id_order = $params[ 'id_order' ];
		$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_POINT;
		$credit->note = $params[ 'note' ];
		$credit->shared = ( $params[ 'shared' ] ? $params[ 'shared' ] : null );
		$credit->save();

		// save log to avoid duplicates
		if( $credit->id_order ){
			$log = new Crunchbutton_Reward_Log;
			$log->id_order = $params[ 'id_order' ];
			$log->points = $params[ 'points' ];
			$log->save();
		}
	}

	public function saveRewardAsCredit( $params ){
		$credit = new Crunchbutton_Credit();
		$credit->id_user = $params[ 'id_user' ];
		$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
		$credit->date = date( 'Y-m-d H:i:s' );
		$credit->value = $params[ 'value' ];
		$credit->id_order = $params[ 'id_order' ];
		$credit->credit_type = $params[ 'credit_type' ];
		$credit->note = $params[ 'note' ];
		$credit->id_referral = $params[ 'id_referral' ];
		$credit->save();
	}

	public function orderWasAlreadySharedFacebook( $id_order ){
		$credit = Crunchbutton_Credit::q( 'SELECT * FROM credit c WHERE c.id_order = ? AND c.type = ? AND credit_type = ? AND ( shared = \'facebook\' OR note LIKE \'%facebook shared%\' ) LIMIT 1', [$id_order, Crunchbutton_Credit::TYPE_CREDIT, Crunchbutton_Credit::CREDIT_TYPE_POINT]);
		if( $credit->id_credit ){
			return true;
		}
		return false;
	}

	public function orderWasAlreadySharedTwitter( $id_order ){
		$credit = Crunchbutton_Credit::q( 'SELECT * FROM credit c WHERE c.id_order = ? AND c.type = ? AND credit_type = ? AND ( shared = \'twitter\' OR note LIKE \'%twitter shared%\' ) LIMIT 1', [$id_order, Crunchbutton_Credit::TYPE_CREDIT, Crunchbutton_Credit::CREDIT_TYPE_POINT]);
		if( $credit->id_credit ){
			return true;
		}
		return false;
	}

	//
	public function processOrder( $id_order, $order = false ){
		if( !$order ){
			$order = Crunchbutton_Order::o( $id_order );
		}

		$this->loadSettings();
		if( $order->id_order ){
			$amount = number_format( $order->final_price_plus_delivery_markup, 2 );
			$cents = $amount * 100;
			$points = $this->calcPointsPerCents( $cents );
			$points = $this->calcOrdersOver( $cents, $points );
			return intval( $points );
		}
		return 0;
	}

	// rewards: 2x after user shares order #3429
	public function sharedOrder( $id_order, $social = 'facebook' ){
		$settings = $this->loadSettings();
		$points = $this->processOrder( $id_order );
		// See: #5026
		switch ( $social ) {
			case 'twitter':
				return ( $points * 2 );
				break;
			case 'facebook':
			default:
				return $points;
				break;
		}
	}

	//
	public function getRefered(){
		$settings = $this->loadSettings();
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_GET_REFERRED_VALUE ], '+', 0 );
	}

	public function pointsToGetDeliveryFree(){
		$settings = $this->loadSettings();
		return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_MAX_CAP_POINTS ], '+', 0 );
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

	public function adminRefersNewUserCreditAmount(){
		if( $this->code ){
			$admin = Crunchbutton_Admin::byInviteCode( $this->code )->get( 0 );
			if( $admin->referral_admin_credit ){
				return floatval( $admin->referral_admin_credit );
			} else {
				if( $admin->isDriver() ){
					$settings = self::loadSettings();
					return floatval( $settings[ Crunchbutton_Reward::CONFIG_KEY_ADMIN_REFER_USER_AMOUNT ] );
				}
			}
		}
	}

	public function refersNewUserCreditAmount(){
		$settings = $this->loadSettings();
		return floatval( $settings[ Crunchbutton_Reward::CONFIG_KEY_REFER_NEW_USER_AMOUNT ] );
	}

	public function getReferredDiscountAmount(){
		if( $this->code ){
			$admin = Crunchbutton_Admin::byInviteCode( $this->code )->get( 0 );
			if( $admin->id_admin ){
				if( $admin->referral_customer_credit ){
					return floatval( $admin->referral_customer_credit );
				} else {
					$settings = $this->loadSettings();
					return floatval( $settings[ Crunchbutton_Reward::CONFIG_KEY_GET_REFERRED_DISCOUNT_AMOUNT ] );
				}

			} else {
				$user = Crunchbutton_User::byInviteCode( $this->code );
				if( $user->id_user ){
					$settings = $this->loadSettings();
					return floatval( $settings[ Crunchbutton_Reward::CONFIG_KEY_GET_REFERRED_DISCOUNT_AMOUNT ] );
				}
			}
		}
		return 0;
	}

	// rewards: 2x points when ordering in same week #3432
	public function orderTwiceSameWeek( $id_user ){
		$query = "SELECT o.* FROM `order` o WHERE o.id_user = '" . $id_user . "' ORDER BY id_order DESC LIMIT 2";
		$orders = Crunchbutton_Order::q( $query );
		if( $orders->count() == 2  ){
			$order_1 = $orders->get( 0 );
			$order_2 = $orders->get( 1 );
			$interval = Crunchbutton_Util::intervalToSeconds( $order_1->date()->diff( $order_2->date() ) );
			if( $interval <= ( 60 * 60 * 24 * 7 ) ){
				$settings = $this->loadSettings();
				$points = $this->processOrder( $order_1->id_order );
				return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_TWICE_SAME_WEEK_VALUE ],
																				$settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_TWICE_SAME_WEEK_OPERATION ],
																				$points );
			}
		}
		return 0;
	}

	// rewards: 4x points when ordering 2 days in a row #3434
	public function orderTwoDaysInARow( $id_user ){
		$query = "SELECT o.* FROM `order` o WHERE o.id_user = '" . $id_user . "' ORDER BY id_order DESC LIMIT 2";
		$orders = Crunchbutton_Order::q( $query );
		if( $orders->count() == 2  ){
			$order_1 = $orders->get( 0 );
			$order_2 = $orders->get( 1 );
			$interval = Crunchbutton_Util::intervalToSeconds( $order_1->date()->diff( $order_2->date() ) );
			if( $interval <= ( 60 * 60 * 24 * 2 ) ){
				$settings = $this->loadSettings();
				$points = $this->processOrder( $order_1->id_order );
				return $this->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_2_DAYS_IN_A_ROW_VALUE ],
																				$settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_2_DAYS_IN_A_ROW_OPERATION ],
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

	public function parseConfigValue( $value, $operation, $points ){
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

	public function createUniqueCode( $name, $phone, $step = 0 ){

		$_name = explode( ' ', $name );

		switch ( $step ) {
			case 3:
				if( $_name[ 2 ] ){
					$code = $_name[ 0 ] . '-' . $_name[ 1 ] . '-' . $_name[ 2 ] . '-' . $phone;
				}
				break;
			case 2:
				if( $_name[ 2 ] ){
					$code = $_name[ 0 ] . $_name[ 1 ] . $_name[ 2 ] . $phone;
				} else {
					$code = $_name[ 0 ] . '-' . $_name[ 1 ] . '-' . $phone;
				}
				break;
			case 1:
				if( $_name[ 1 ] ){
					$code = $_name[ 0 ] . $_name[ 1 ] . $phone;
				} else {
					$code = $_name[ 0 ] . '-' . $phone;
				}
				break;
			case 0:
				$code = $_name[ 0 ] . trim( $phone );
				break;
		}

		if( $code ){
			$referral = new Crunchbutton_Reward;
			if( !$referral->validateInviteCode( $code ) ){
				return $code;
			} else {
				$step++;
				return Crunchbutton_Reward::createUniqueCode( $name, $phone, $step );
			}
		}
		return false;
	}

	public function loadSettings(){
		if( !$this->_config ){
			$configs = Crunchbutton_Config::q( "SELECT * FROM config WHERE `key` LIKE 'reward_points%'" );
			foreach ( $configs as $config ) {
				$value = $config->value ? $config->value : 0;
				$this->_config[ $config->key ] = $value;
			}
		}
		return $this->_config;
	}
}