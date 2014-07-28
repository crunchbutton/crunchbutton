<?php

class Crunchbutton_Reward_Retroactively extends Cana_Table{

	public function start(){

		$usersLimit = 10;
		$usersStartAt = 0;

		$reward = new Crunchbutton_Reward;

		$settings = $reward->loadSettings();

		// select all the users
		$users = Crunchbutton_User::q( 'SELECT * FROM user WHERE id_user = 13773');
		// $users = Crunchbutton_User::q( 'SELECT * FROM user ORDER BY id_user DESC LIMIT ' . $usersStartAt  . ',' . $usersLimit );
		foreach( $users as $user ){
			// get its orders
			$orders = Crunchbutton_Order::q( 'SELECT * FROM `order` o WHERE o.id_user = "' . $user->id_user . '" ORDER BY id_order DESC' );
			foreach( $orders as $order ){

				// check if the order was already rewarded
				if( $this->checkIfOrderWasAlreadyRewarded( $order->id_order ) ){
					continue;
				}

				// at first convert the order's amount to points
				$points = $reward->processOrder( $order->id_order, $order );
				// check it the user should earn points by order twice in a row
				$last_order = Crunchbutton_Order::q( "SELECT o.* FROM `order` o WHERE o.id_user = '" . $order->id_user . "' AND id_order < '" . $order->id_order . "' ORDER BY id_order DESC LIMIT 1" );
				// if it has last order
				if( $last_order->get( 0 )->id_order ){
					$last_order = $last_order->get( 0 );
					$interval = $order->date()->diff( $last_order->date() );
					$interval = Crunchbutton_Util::intervalToSeconds( $interval );
					// rewards: 4x points when ordering 2 days in a row #3434
					if( $interval <= ( 60 * 60 * 24 * 2 ) ){
						$points += $reward->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_2_DAYS_IN_A_ROW_VALUE ],
																									$settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_2_DAYS_IN_A_ROW_OPERATION ],
																									$points );
					} else {
						// // rewards: 2x points when ordering in same week #3432
						if( $interval <= ( 60 * 60 * 24 * 7 ) ){
							$points += $reward->parseConfigValue( $settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_TWICE_SAME_WEEK_VALUE ],
																										$settings[ Crunchbutton_Reward::CONFIG_KEY_ORDER_TWICE_SAME_WEEK_OPERATION ],
																										$points );
						}
					}
				}
				$params = [ 'id_order' => $order->id_order, 'id_user' => $order->id_user, 'points' => $points, 'note' => 'retroactively points' ];
				$reward->saveReward( $params );
				$log = new Crunchbutton_Reward_Retroactively;
				$log->id_order = $order->id_order;
				$log->points = $points;
				$log->save();
			}
		}
	}

	public function checkIfOrderWasAlreadyRewarded( $id_order ){
		$reward = Crunchbutton_Reward_Retroactively::q( 'SELECT * FROM reward_retroactively r WHERE r.id_order = "' . $id_order . '"  LIMIT 1' );
		if( $reward->id_reward_retroactively ){
			return true;
		}
		return false;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('reward_retroactively')
			->idVar('id_reward_retroactively')
			->load($id);
	}
}