<?php

class Crunchbutton_Reward_Retroactively extends Cana_Table{

	public function start(){

		$iteraction = 1;

		$limit = 2000;
		$startingAt = ( $limit * $iteraction );

		$reward = new Crunchbutton_Reward;

		$settings = $reward->loadSettings();

		$totalPoints = 0;

		// select all the users
		$users = Crunchbutton_User::q( 'SELECT * FROM user ORDER BY id_user DESC LIMIT ' . $startingAt . ',' . $limit );
		foreach( $users as $user ){

			$id_user = $user->id_user;

			// get its orders
			$orders = Crunchbutton_Order::q( 'SELECT * FROM `order` o WHERE o.id_user = "' . $id_user . '" ORDER BY id_order DESC' );
			foreach( $orders as $order ){

				$id_order = $order->id_order;

				// check if the order was already rewarded
				if( !Crunchbutton_Reward_Log::checkIfOrderWasAlreadyRewarded( $id_order ) ){

					// at first convert the order's amount to points
					$points = $reward->processOrder( $id_order, $order );
					// check it the user should earn points by order twice in a row
					$last_order = Crunchbutton_Order::q( "SELECT o.* FROM `order` o WHERE o.id_user = '" . $id_user . "' AND id_order < '" . $id_order . "' ORDER BY id_order DESC LIMIT 1" );
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

					$params = [ 'id_order' => $id_order, 'id_user' => $id_user, 'points' => $points, 'note' => 'retroactively points' ];
					$reward->saveReward( $params );
					$totalPoints += $points;
				}
			}
		}
		return $totalPoints;
	}
}