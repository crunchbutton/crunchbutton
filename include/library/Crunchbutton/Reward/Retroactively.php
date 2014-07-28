<?php

class Crunchbutton_Reward_Retroactively extends Cana_Table{

	public function start(){

		$usersLimit = 10;
		$usersStartAt = 0;

		$reward = new Crunchbutton_Reward;

		$settings = $reward->loadSettings();

		// select all the users
		$users = Crunchbutton_User::q( 'SELECT * FROM user ORDER BY id_user DESC LIMIT ' . $usersStartAt  . ',' . $usersLimit );
		foreach( $users as $user ){
			// get its orders
			$orders = $users->orders();
			foreach( $orders as $order ){
				// at first convert the order's amount to points
				$points = $reward->processOrder( $order->id_order, $order );
				// check it the user should earn points by order twice in a row
				$last_order = Crunchbutton_Order::q( "SELECT o.* FROM `order` o WHERE o.id_user = '" . $order->id_user . "' AND id_order < '" . $order->id_order . "' ORDER BY id_order DESC LIMIT 1" );
				// if it has last order
				if( $last_order->get( 0 )->id_order ){
					$last_order = $last_order->get( 0 );
					$interval = $order->date()->diff( $last_order->date() );
					$interval = Crunchbutton_Util::intervalToSeconds( $interval );
					if( $interval <= ( 60 * 60 * 24 * 2 ) ){
						$points += $reward->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_2_DAYS_IN_A_ROW_VALUE ],
																						$settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_2_DAYS_IN_A_ROW_OPERATION ],
																						$points );
					}
				}
echo '<pre>';var_dump( $order->id_order, $points );exit();
			}
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('referral_retroactively')
			->idVar('id_referral_retroactively')
			->load($id);
	}
}