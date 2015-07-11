<?php

ini_set('memory_limit', '-1');

class Crunchbutton_Reward_Retroactively extends Cana_Table{

	public function recalculateCreditsFromOrder( $id_order, $order = false ){
		$totalPoints = 0;
		$log = Crunchbutton_Reward_Log::q( 'SELECT * FROM reward_log r WHERE r.id_order = "' . $id_order . '" ORDER BY id_reward_log DESC LIMIT 1' );
		$reward = new Crunchbutton_Reward;
		if( !$order ){
			$order = Order::o( $id_order );
		}
		if( $log->points ){
			$pointsGiven = $log->points;
		} else {
			$pointsGiven = $log->points;
		}
		$points = $reward->processOrder( $id_order, $order );
		$missingPoints = $points - $pointsGiven;
		// The customer should receive more points
		if( $missingPoints > 0 ){
			$points = $missingPoints;
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
			$id_user = $order->id_user;
			$params = [ 'id_order' => $id_order, 'id_user' => $id_user, 'points' => $points, 'note' => 'retroactively points' ];
			$reward->saveReward( $params );
			$totalPoints += $points;
		}
		return $totalPoints;
	}

	public function rewardReferralRetroactively(){

		// $inviterCredits = Crunchbutton_Referral::getInviterCreditValue();
		// $invitedCredits = Crunchbutton_Referral::getInvitedCreditValue();
		$reward = new Crunchbutton_Reward;
		$inviterCredits = $reward->getReferNewUser();
		$invitedCredits = $reward->getRefered();

		$referrals = Crunchbutton_Referral::q( 'SELECT * FROM referral WHERE id_user_inviter IS NOT NULL AND new_user = true' );
		foreach( $referrals as $referral ){

			$inviter_credits = Crunchbutton_Credit::q('
				SELECT * FROM credit
				WHERE
					id_user = ?
					AND id_referral = ?
					AND credit_type = ?
				ORDER BY id_credit DESC
				LIMIT 1
			', [$referral->id_user_inviter, $referral->id_referral, Crunchbutton_Credit::CREDIT_TYPE_CASH]);
			$addInviterCredit = 0;
			if( !$inviter_credits->id_credit ){
				$addInviterCredit = $inviterCredits;
			} else {
				$addInviterCredit = 0;
			}
			if( $addInviterCredit > 0 ){
				$credit = new Crunchbutton_Credit();
				$credit->id_user = $referral->id_user_inviter;
				$credit->id_referral = $referral->id_referral;
				$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
				$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_POINT;
				$credit->date = date('Y-m-d H:i:s');
				$credit->value = $addInviterCredit;
				$credit->paid_by = 'crunchbutton';
				$credit->note = 'Referral inviter: ' . $referral->id_referral;
				Log::debug([ 'referral_type' => 'inviter', 'id_user' => $credit->id_user,  'id_referral' => $credit->id_referral,  'type' => $credit->type,  'date' => $credit->date,  'value' => $credit->value,  'paid_by' => $credit->paid_by,  'note' => $credit->note, 'type' => 'referral' ]);
				$credit->save();
			}

			$invited_credits = Crunchbutton_Credit::q('
				SELECT * FROM credit
				WHERE id_user = ?
				AND id_referral = ?
				AND credit_type = ?
				ORDER BY id_credit DESC LIMIT 1
			', [$referral->id_user_invited, $referral->id_referral, Crunchbutton_Credit::CREDIT_TYPE_CASH]);
			$addInvitedCredit = 0;
			if( !$invited_credits->id_credit ){
				$addInvitedCredit = $invitedCredits;
			} else {
				$addInvitedCredit = 0;
			}
			if( $addInvitedCredit > 0 ){
				$credit = new Crunchbutton_Credit();
				$credit->id_user = $referral->id_user_invited;
				$credit->id_referral = $referral->id_referral;
				$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
				$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_POINT;
				$credit->date = date('Y-m-d H:i:s');
				$credit->value = $addInvitedCredit;
				$credit->paid_by = 'crunchbutton';
				$credit->note = 'Referral invited: ' . $referral->id_referral;
				Log::debug([ 'referral_type' => 'invited', 'id_user' => $credit->id_user,  'id_referral' => $credit->id_referral,  'type' => $credit->type,  'date' => $credit->date,  'value' => $credit->value,  'paid_by' => $credit->paid_by,  'note' => $credit->note, 'type' => 'referral' ]);
				$credit->save();
			}

			/*
			if( !$inviter_credits->id_credit ){
				$addInvitedCredit = $inviterCredits;
			} else {
				$addInvitedCredit = $inviterCredits - $inviter_credits->value;
			}

			if( $addInvitedCredit > 0 ){
				$credit = new Crunchbutton_Credit();
				$credit->id_user = $referral->id_user_inviter;
				$credit->id_referral = $referral->id_referral;
				$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
				$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_CASH;
				$credit->date = date('Y-m-d H:i:s');
				$credit->value = $addInvitedCredit;
				$credit->paid_by = 'crunchbutton';
				$credit->note = 'Referral inviter: ' . $referral->id_referral;
				Log::debug([ 'referral_type' => 'inviter', 'id_user' => $credit->id_user,  'id_referral' => $credit->id_referral,  'type' => $credit->type,  'date' => $credit->date,  'value' => $credit->value,  'paid_by' => $credit->paid_by,  'note' => $credit->note, 'type' => 'referral' ]);
				$credit->save();
			}

			$invited_credits = Crunchbutton_Credit::q( 'SELECT * FROM credit WHERE id_user = "' . $referral->id_user_invited . '" AND id_referral = "' . $referral->id_referral . '" ORDER BY id_credit DESC LIMIT 1' );
			$addInviterCredit = 0;
			if( !$invited_credits->id_credit ){
				$addInviterCredit = $inviterCredits;
			} else {
				$addInviterCredit = $inviterCredits - $invited_credits->value;
			}
			if( $addInviterCredit > 0 ){
				$credit = new Crunchbutton_Credit();
				$credit->id_user = $referral->id_user_invited;
				$credit->id_referral = $referral->id_referral;
				$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
				$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_CASH;
				$credit->date = date('Y-m-d H:i:s');
				$credit->value = $addInviterCredit;
				$credit->paid_by = 'crunchbutton';
				$credit->note = 'Referral invited: ' . $referral->id_referral;
				Log::debug([ 'referral_type' => 'invited', 'id_user' => $credit->id_user,  'id_referral' => $credit->id_referral,  'type' => $credit->type,  'date' => $credit->date,  'value' => $credit->value,  'paid_by' => $credit->paid_by,  'note' => $credit->note, 'type' => 'referral' ]);
				$credit->save();
			}
			*/


		}
	}

	public function start(){

		$iteraction = intval( $_GET[ 'iteraction' ] ) ? intval( $_GET[ 'iteraction' ] ) : 1;

		$limit = intval( $_GET[ 'limit' ] ) ? intval( $_GET[ 'limit' ] ) : 2000;
		$startingAt = ( $limit * $iteraction );

		$reward = new Crunchbutton_Reward;

		$settings = $reward->loadSettings();

		$totalPoints = 0;
		$totalPointsRecalculated = 0;

		// select all the users
		// $users = Crunchbutton_User::q( 'SELECT DISTINCT( u.id_user ), u.* FROM user u INNER JOIN user_auth ua ON ua.id_user = u.id_user ORDER BY id_user DESC LIMIT ' . $startingAt . ',' . $limit );
		$users = Crunchbutton_User::q( 'SELECT DISTINCT( u.id_user ), u.* FROM user u
  INNER JOIN user_auth ua ON ua.id_user = u.id_user
  INNER JOIN `order` o ON o.id_user = u.id_user AND date > DATE_SUB(NOW(), INTERVAL 1 WEEK )
  ORDER BY id_user ' );



		foreach( $users as $user ){

			$id_user = $user->id_user;

			// get its orders
			$orders = Crunchbutton_Order::q( 'SELECT * FROM `order` o WHERE o.id_user = "' . $id_user . '" ORDER BY id_order DESC' );
			foreach( $orders as $order ){

				$id_order = $order->id_order;

				$finalPoints = 0;

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
					$finalPoints = $points;
					$totalPoints += $points;
				} else {
					$points = Crunchbutton_Reward_Retroactively::recalculateCreditsFromOrder( $id_order, $order );
					$finalPoints = $points;
					$totalPointsRecalculated += $points;
				}
				// check if the order was shared
				$credit = Crunchbutton_Credit::q( 'SELECT * FROM credit c WHERE c.id_order = ? AND c.type = ? AND credit_type = ? AND note LIKE "%sharing%" LIMIT 1', [$id_order, Crunchbutton_Credit::TYPE_CREDIT, Crunchbutton_Credit::CREDIT_TYPE_POINT]);
				if( $credit->id_credit ){
					$pointsPerSharing = $credit->value;
					$points = $reward->sharedOrder( $id_order );
					$missingPoints = $points - $pointsPerSharing;
					if( $missingPoints > 0 ){
						$params = [ 'id_order' => $id_order, 'id_user' => $id_user, 'points' => $points, 'note' => 'retroactively points for sharing' ];
						$reward->saveReward( $params );
					}
				}
			}
		}
		return [ 'total' => $totalPoints, 'recalculate' => $totalPointsRecalculated ];
	}
}